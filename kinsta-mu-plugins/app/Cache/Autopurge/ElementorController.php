<?php

namespace Kinsta\KMP\Cache\Autopurge;

use function class_exists;

final class ElementorController extends Controller
{
	protected string $name = 'elementor_controller';

	public function hook(): void
	{
		add_action('elementor/core/files/clear_cache', [$this, 'purge']);
		add_action('elementor/maintenance_mode/mode_changed', [$this, 'purge']);
	}

	public function isSupported(): bool
	{
		return class_exists('\Elementor\Plugin');
	}

	public function getDescription(): string
	{
		return __('Purge cache on Elementor updates that affect the front-end.', 'kinsta-mu-plugins');
	}
}
