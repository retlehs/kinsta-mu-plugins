<?php

namespace Kinsta\KMP\Compat;

use Kinsta\Cache_Purge;
use Kinsta\KMP;
use Kinsta\KMP\Cache\Autopurge;

final class Elementor extends Autopurge\Controller
{
    protected string $name = 'elementor_controller';

	public function hook(): void
	{
		add_action('elementor/core/files/clear_cache', [$this, 'clear']);
		add_action('elementor/maintenance_mode/mode_changed', [$this, 'clear']);
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
