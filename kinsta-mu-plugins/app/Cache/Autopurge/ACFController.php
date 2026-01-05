<?php

namespace Kinsta\KMP\Cache\Autopurge;

use Kinsta\Cache_Purge;
use Kinsta\KMP;
use Kinsta\KMP\Cache\Autopurge;

final class ACFController extends Controller
{
    protected string $name = 'acf_controller';

	public function hook(): void
	{
        $optionsPageSlugs = apply_filters('kinsta/kmp/cache/autopurge/acf/options_page_slugs', null);

        add_action('acf/options_page/save', function ($postId, $slug) use ($optionsPageSlugs) {
            if (is_array($optionsPageSlugs) && !in_array($slug, $optionsPageSlugs, true)) {
                return;
            }

            $this->clear();
        }, 10, 2);
	}

    public function isSupported(): bool
    {
        return class_exists( 'ACF' );
    }

    public function getDescription(): string
    {
        return __('Purge cache when ACF options are updated.', 'kinsta-mu-plugins');
    }
}
