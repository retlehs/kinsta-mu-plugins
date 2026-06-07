<?php

namespace Kinsta\KMP\Cache\Autopurge;

use Theme_Upgrader;

use function in_array;
use function is_array;

class WPThemeController extends Controller
{
	protected string $name = 'wp_theme_controller';

	public function hook(): void
	{
		add_action('switch_theme', [$this, 'purge']);
		add_action('upgrader_process_complete', [$this, 'onUpgraderProcessComplete'], 10, 2);
	}

	public function getDescription(): string
	{
		return __('Purge cache when the theme is updated or switched.', 'kinsta-mu-plugins');
	}

	/**
	 * @param mixed $upgrader
	 * @param mixed $options
	 */
	public function onUpgraderProcessComplete($upgrader, $options): void
	{
		if (! ($upgrader instanceof Theme_Upgrader)) {
			return;
		}

		$options = wp_parse_args((array) $options, [
			'action' => null,
			'type'   => null,
			'themes' => [],
		]);

		if (
			$options['action'] !== 'update' ||
			$options['type'] !== 'theme' ||
			! isset($options['themes']) ||
			! is_array($options['themes'])
		) {
			return;
		}

		$currentTheme = get_stylesheet();

		if (! in_array($currentTheme, $options['themes'], true)) {
			return;
		}

		$this->purge();
	}
}
