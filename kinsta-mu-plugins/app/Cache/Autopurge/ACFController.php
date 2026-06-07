<?php

namespace Kinsta\KMP\Cache\Autopurge;

use function class_exists;
use function in_array;
use function is_array;

final class ACFController extends Controller
{
	protected string $name = 'acf_controller';

	public function hook(): void
	{
		$optionsPageSlugs = apply_filters('kinsta/kmp/cache/autopurge/acf/options_page_slugs', null);

		add_action('acf/options_page/save', function ($postId, $slug) use ($optionsPageSlugs): void {
			if (is_array($optionsPageSlugs) && ! in_array($slug, $optionsPageSlugs, true)) {
				return;
			}

			$this->purge();
		}, 10, 2);
	}

	public function isSupported(): bool
	{
		return class_exists('ACF');
	}

	public function getDescription(): string
	{
		return __('Purge cache when ACF options are updated.', 'kinsta-mu-plugins');
	}
}
