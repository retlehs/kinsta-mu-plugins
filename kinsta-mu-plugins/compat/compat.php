<?php
/**
 * Initialize platform and plugin compatibility with WordPress
 * and other entities such as plugins, themes, and WP-CLI).
 *
 * @package KinstaMUPlugins/Compat
 */

namespace Kinsta\Compat;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

// Load files that will handle 3rd-party plugins compatibility.
require_once plugin_dir_path( __FILE__ ) . 'third-party/wordfence.php';
require_once plugin_dir_path( __FILE__ ) . 'third-party/swift-performance.php';
// Stub class for old WP Rocket versions.
require_once plugin_dir_path( __FILE__ ) . 'third-party/class-cdn-enabler.php';
require_once plugin_dir_path( __FILE__ ) . 'third-party/wp-rocket.php';
