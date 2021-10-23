<?php
/**
 * Plugin Name: Kinsta Must-use Plugins
 * Plugin URI: https://kinsta.com/knowledgebase/kinsta-mu-plugin/
 * Description: The plugin designed to work on Kinsta's managed WordPress hosting platform.
 * Version: 2.3.4
 * Author: Kinsta Team
 * Author URI: https://kinsta.com/about-us/
 * Text Domain: kinsta-mu-plugins
 * Domain Path: /kinsta-mu-plugins/shared/translations
 *
 * @package KinstaMUPlugins
 */

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

define( 'KINSTAMU_VERSION', '2.3.4' );
if ( ! defined( 'KINSTAMU_WHITELABEL' ) ) {
	define( 'KINSTAMU_WHITELABEL', false );
}

require_once plugin_dir_path( __FILE__ ) . 'kinsta-mu-plugins/shared/class-shared.php';
require_once plugin_dir_path( __FILE__ ) . 'kinsta-mu-plugins/compat/compat.php';
require_once plugin_dir_path( __FILE__ ) . 'kinsta-mu-plugins/cache/cache.php';
require_once plugin_dir_path( __FILE__ ) . 'kinsta-mu-plugins/cdn/cdn.php';
