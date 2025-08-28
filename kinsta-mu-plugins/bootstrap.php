<?php
/**
 * Bootstrap classes and functions for the Kinsta MU Plugin.
 */

use Kinsta\KMP;
use Kinsta\KMP\Compat\Elementor;

global $kinsta_muplugin;
global $kinsta_cache;
global $KinstaCache; // phpcs:ignore

$kinsta_muplugin = new KMP();
$kinsta_cache = $kinsta_muplugin;
$KinstaCache = $kinsta_muplugin; // phpcs:ignore

/** Ensure Elementor is loaded before initializing the Elementor compatibility. */
add_action('elementor/init', static fn () => new Elementor($kinsta_muplugin));
