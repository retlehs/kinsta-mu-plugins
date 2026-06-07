<?php

namespace Kinsta\KMP\Cache\Autopurge;

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
		add_action('save_post', [$this, 'onSavePost'], 10, 3);
		add_action('transition_post_status', [$this, 'onPostStatusChange'], 10, 3);
	}

	public function getDescription(): string
	{
		return __('Purge cache when posts are updated.', 'kinsta-mu-plugins');
	}

	public function onSavePost(int $postId, WP_Post $post, bool $update): void
	{
		if (! $this->shouldProceed()) {
			return;
		}

		if ($update !== true || ! $this->isPostPublished($postId)) {
			return;
		}

		$yoastOriginalId = $this->getYoastRepublishOriginalId($postId);
		$targetPostId = $yoastOriginalId ?? $postId;

		$this->kmp->kinsta_cache_purge->purge_single_happened = true;
		$this->kmp->kinsta_cache_purge->initiate_purge($targetPostId);

		$context = [
			'controller' => __METHOD__,
			'post_id' => $targetPostId,
		];
		if ($yoastOriginalId !== null) {
			$context['yoast_duplicate_post_clone_id'] = $postId;
		}

		debug_log('Post cache clearing was initiated.', $context);
	}

	public function onPostStatusChange(string $newStatus, string $oldStatus, WP_Post $post): void
	{
		// Do not proceed if the status is not changing.
		if (! $this->shouldProceed() || $newStatus === $oldStatus) {
			return;
		}

		// If post is published or was published before, we need to purge the cache.
		if (! $this->isPostPublished($post->ID) && $oldStatus !== 'publish') {
			return;
		}

		$yoastOriginalId = $this->getYoastRepublishOriginalId($post->ID);
		$targetPostId = $yoastOriginalId ?? $post->ID;

		$this->kmp->kinsta_cache_purge->purge_single_happened = true;
		$this->kmp->kinsta_cache_purge->initiate_purge($targetPostId);

		$context = [
			'controller' => __METHOD__,
			'post_id' => $targetPostId,
			'post_status_new' => $newStatus,
			'post_status_old' => $oldStatus,
		];
		if ($yoastOriginalId !== null) {
			$context['yoast_duplicate_post_clone_id'] = $post->ID;
		}

		debug_log('Post cache clearing was initiated.', $context);
	}

	private function isPostPublished(int $postId): bool
	{
		return ! wp_is_post_autosave($postId) && ! wp_is_post_revision($postId) && get_post_status($postId) === 'publish';
	}

	/**
	 * Return the original post ID for a Yoast Duplicate Post "Rewrite & Republish"
	 * clone, or `null` when the given post is not such a clone. The clone briefly
	 * publishes before its content is copied onto the original, so we purge the
	 * original's URL to match what readers actually see.
	 */
	private function getYoastRepublishOriginalId(int $postId): ?int
	{
		if (get_post_meta($postId, '_dp_is_rewrite_republish_copy', true) !== '1') {
			return null;
		}

		$originalId = (int) get_post_meta($postId, '_dp_original', true);

		return $originalId > 0 ? $originalId : null;
	}
}
