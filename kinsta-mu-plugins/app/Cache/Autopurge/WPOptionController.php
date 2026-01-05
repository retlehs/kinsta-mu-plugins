<?php

namespace Kinsta\KMP\Cache\Autopurge;

use Kinsta\Cache_Purge;
use Kinsta\KMP;
use Kinsta\KMP\Cache\Autopurge;

/**
 * The controller that trigger the cache purge when the options are updated.
 */
final class WPOptionController extends Controller
{
	protected string $name = 'wp_option_controller';

    /**
	 * List of built-in WordPress option names that should trigger a cache purge
	 * when updated.
	 */
	private const DEFAULT_OPTIONS = [
		'blogname',
		'blogdescription',
		'date_format',
		'time_format',
		'language',
	];

	public function hook(): void
	{
        /**
         * Filter to control the list of option names that should trigger a cache purge
         * when updated. By default, it includes some common WordPress options.
         *
         * @param array $optionNames List of option names.
         */
        $optionNames = apply_filters('kinsta/kmp/cache/autopurge/wp/options', self::DEFAULT_OPTIONS);

        add_action('updated_option', function ($optionName) use ($optionNames) {
            if (in_array($optionName, $optionNames, true)) {
                $this->clear();
            }
        });
    }

    public function getDescription(): string
    {
        return __('Purge cache when options are updated.', 'kinsta-mu-plugins');
    }
}
