<?php

namespace Kinsta\KMP\Cache\Autopurge;

use Kinsta\Cache_Purge;
use Kinsta\KMP;
use Kinsta\KMP\Cache\Autopurge;
use WP_Post;

use function Kinsta\KMP\debug_log;

/**
 * Handle cache purge when WordPress posts are updated.
 */
final class WPPostController extends Controller
{
	protected string $name = 'wp_post_controller';

	public function hook(): void
	{
        /**
         * @see kinsta-mu-plugins/cache/class-cache-purge.php
         * @todo Move all the "post" related hooks from `class-cache-purge.php` to this class.
         */
		add_action( 'save_post', array( $this, 'onSavePost' ), 10, 3 );
    }

    public function getDescription(): string
    {
        return __('Purge cache when posts are updated.', 'kinsta-mu-plugins');
    }

    public function onSavePost(int $postId, WP_Post $post, bool $update): void
    {
        if ( ! $this->kmp->kinsta_cache_purge instanceof Cache_Purge ) {
            return;
        }

        if ( $this->kmp->kinsta_cache_purge->purge_single_happened ) { // Avoid multiple purges on the same request.
			return;
		}

		if ( true === $update && $this->isPostPublished( $postId ) ) {
			$this->kmp->kinsta_cache_purge->purge_single_happened = true;
			$this->kmp->kinsta_cache_purge->initiate_purge( $postId );

            debug_log('Post cache clearing was initiated.', ['controller' => $this->name, 'post_id' => $postId]);
		}
    }

    private function isPostPublished(int $postId): bool
    {
        return ! wp_is_post_autosave( $postId ) && ! wp_is_post_revision( $postId ) && 'publish' === get_post_status( $postId );
    }
}
