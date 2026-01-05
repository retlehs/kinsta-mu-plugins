<?php

namespace Kinsta\KMP\Cache\Autopurge;

use Kinsta\Cache_Purge;
use Kinsta\KMP;
use Kinsta\KMP\Cache\Autopurge;

/**
 * The controller that trigger the cache purge when the WordPress built-in
 * "Custom Headers" on the theme is updated.
 *
 * @see https://developer.wordpress.org/themes/functionality/custom-headers/
 */
class WPThemeHeaderController extends Controller
{
    protected string $name = 'wp_theme_header_controller';

	public function hook(): void
	{
        $theme = get_option( 'stylesheet' );

        /**
         * Hook into the update of the theme mods option where the theme header
         * is stored.
         *
         * @see https://github.com/WordPress/WordPress/blob/master/wp-admin/includes/class-custom-image-header.php#L1173-L1221
         * @see https://github.com/WordPress/WordPress/blob/master/wp-includes/theme.php#L1095-L1116
         */
        add_action('update_option_theme_mods_' . $theme, [$this, 'clear']);
	}

    public function isSupported(): bool
    {
        return current_theme_supports('custom-header');
    }

    public function getDescription(): string
    {
        return __('Purge cache when theme headers are updated.', 'kinsta-mu-plugins');
    }
}
