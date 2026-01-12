<?php

namespace Kinsta\KMP\Cache\Autopurge;

use Kinsta\Cache_Purge;
use Kinsta\KMP;
use Theme_Upgrader;
use WP_Upgrader;

class WPThemeController extends Controller
{
	protected string $name = 'wp_theme_controller';

	public function hook(): void
	{
        add_action('switch_theme', [$this, 'clear']);
        add_action('upgrader_process_complete', [$this, 'onUpgraderPorcessComplete'], 10, 2);
    }

    public function getDescription(): string
    {
        return __('Purge cache when the theme is updated or switched.', 'kinsta-mu-plugins');
    }

    /**
     * @param mixed $upgrader
     * @param mixed $options
     * @return void
     */
    public function onUpgraderPorcessComplete($upgrader, $options): void
    {
        if (!($upgrader instanceof Theme_Upgrader)) {
            return;
        }

        $options = wp_parse_args((array) $options, [
            'action' => null,
            'type'   => null,
            'themes' => [],
        ]);

        if (
            $options['action'] === 'update' &&
            $options['type'] === 'theme' &&
            isset($options['themes']) &&
            is_array($options['themes'])
        ) {
            $currentTheme = get_stylesheet();

            if (in_array($currentTheme, $options['themes'], true)) {
                $this->clear();
            }
        }
    }
}
