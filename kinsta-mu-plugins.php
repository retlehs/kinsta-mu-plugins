<?php
/**
 * Plugin Name: Kinsta Must-use Plugins
 * Plugin URI: https://kinsta.com/knowledgebase/kinsta-mu-plugin/
 * Description: The plugin designed to work on Kinsta's managed WordPress hosting platform.
 * Version: 3.0.0
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

define( 'KINSTAMU_VERSION', '3.0.0' );
define( 'KMP_DOCS_URL', 'https://kinsta.com/help/kinsta-mu-plugin/' );

if ( ! defined( 'KINSTAMU_WHITELABEL' ) ) {
	define( 'KINSTAMU_WHITELABEL', false );
}

require_once plugin_dir_path( __FILE__ ) . 'kinsta-mu-plugins/utils/utils.php';

require_once plugin_dir_path( __FILE__ ) . 'kinsta-mu-plugins/admin/class-kmp-admin.php';
require_once plugin_dir_path( __FILE__ ) . 'kinsta-mu-plugins/cache/class-cache.php';
require_once plugin_dir_path( __FILE__ ) . 'kinsta-mu-plugins/cache/class-cache-purge.php';
require_once plugin_dir_path( __FILE__ ) . 'kinsta-mu-plugins/security/class-banned-plugins.php';
require_once plugin_dir_path( __FILE__ ) . 'kinsta-mu-plugins/wp-cli/class-kmp-wpcli.php';

require_once plugin_dir_path( __FILE__ ) . 'kinsta-mu-plugins/class-kmp.php';

require_once plugin_dir_path( __FILE__ ) . 'kinsta-mu-plugins/compat/compat.php';
