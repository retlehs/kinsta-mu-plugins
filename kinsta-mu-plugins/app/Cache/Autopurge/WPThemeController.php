<?php

namespace Kinsta\KMP\Cache\Autopurge;

use Kinsta\Cache_Purge;
use Kinsta\KMP;
use Theme_Upgrader;
use WP_Upgrader;

final class WPThemeController extends Controller
{
	protected string $name = 'wp_theme_controller';

	public function hook(): void
	{
        add_action('switch_theme', [$this, 'clear']);
        add_action('upgrader_process_complete', function ($upgrader, $options) {
            if (!($upgrader instanceof Theme_Upgrader)) {
                return;
            }

            if ($options['action'] === 'update' && $options['type'] === 'theme') {
                $currentTheme = get_stylesheet();

                if (in_array($currentTheme, $options['themes'], true)) {
                    $this->clear();
                }
            }
        }, 10, 2);
    }

    public function getDescription(): string
    {
        return __('Purge cache when the theme is updated or switched.', 'kinsta-mu-plugins');
    }
}
