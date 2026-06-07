<?php

declare(strict_types=1);

namespace Kinsta\KMP\Cache\Autopurge;

use const PHP_INT_MIN;

/**
 * Handle the cache clear when Widgets options are updated.
 */
final class WPThemeWidgetController extends Controller
{
	protected string $name = 'wp_theme_widget_controller';

	/**
	 * Default to be disabled.
	 */
	protected ?bool $default = false;

	public function hook(): void
	{
		/**
		 * Trigger cache clear when a widget form  is updated.
		 */
		add_filter('widget_update_callback', function ($instance) {
			$this->purge();

			return $instance; // Return the instance to continue the update process.
		}, PHP_INT_MIN);

		/**
		 * Trigger cache clear when the sidebar location is changed, such as
		 * when a widget is added, or removed from a sidebar.
		 *
		 * On the legacy widgets screen, this is triggered immediately when user
		 * moved a widget to a different sidebar.
		 *
		 * On the block widgets screen, this is triggered when the user clicks
		 * the "Update" button after making changes to the widget.
		 */
		add_action('update_option_sidebars_widgets', [$this, 'purge'], PHP_INT_MIN);
	}

	public function isSupported(): bool
	{
		return current_theme_supports('widgets');
	}

	public function getDescription(): string
	{
		return __('Purge cache when theme widgets are updated.', 'kinsta-mu-plugins');
	}
}
