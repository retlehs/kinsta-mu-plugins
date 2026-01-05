<?php

/**
 * Plugin Name: Kinsta Must-use Plugins
 * Plugin URI: https://kinsta.com/knowledgebase/kinsta-mu-plugin/
 * Description: The plugin designed to work on Kinsta's managed WordPress hosting platform.
 * Version: 3.4.0
 * Author: Kinsta Team
 * Author URI: https://kinsta.com/about-us/
 * Text Domain: kinsta-mu-plugins
 * Domain Path: /kinsta-mu-plugins/shared/translations
 */

namespace Kinsta\KMP;

if (! defined('ABSPATH')) { // If this file is called directly.
	die('No script kiddies please!');
}

define('KINSTAMU_VERSION', '3.4.0');
define('KMP_DOCS_URL', 'https://kinsta.com/docs/wordpress-hosting/kinsta-mu-plugin');

if (! defined('KINSTAMU_WHITELABEL')) {
	define('KINSTAMU_WHITELABEL', false);
}

/**
 * Define the directory path to the plugin file.
 *
 * This constant provides a convenient reference to the plugin's directory path,
 * useful for including or requiring files relative to this directory.
 *
 * @example
 *
 * if (defined('\Kinsta\KMP\PLUGIN_DIR')) {
 * // Do something when PLUGIN_DIR is defined.
 * };
 */
const PLUGIN_DIR = __DIR__;

/**
 * Define the path to the plugin file.
 *
 * This path can be used in various contexts, such as managing the activation
 * and deactivation processes, loading the plugin text domain, adding action
 * links, and more.
 *
 * if (defined('\Kinsta\KMP\PLUGIN_FILE')) {
 * // Do something when PLUGIN_FILE is defined.
 * };
 */
const PLUGIN_FILE = __FILE__;

/**
 * Load dependencies using the Composer autoloader.
 *
 * This allows us to load third-party libraries without having to include
 * or require the files from the libraries manually.
 *
 * @see https://getcomposer.org/doc/01-basic-usage.md#autoloading
 */
require PLUGIN_DIR . '/kinsta-mu-plugins/vendor/autoload.php';

require_once plugin_dir_path(__FILE__) . 'kinsta-mu-plugins/utils/utils.php';
require_once plugin_dir_path(__FILE__) . 'kinsta-mu-plugins/admin/class-kmp-admin.php';
require_once plugin_dir_path(__FILE__) . 'kinsta-mu-plugins/cache/class-cache.php';
require_once plugin_dir_path(__FILE__) . 'kinsta-mu-plugins/cache/class-cache-purge.php';
require_once plugin_dir_path(__FILE__) . 'kinsta-mu-plugins/security/class-banned-plugins.php';
require_once plugin_dir_path(__FILE__) . 'kinsta-mu-plugins/wp-cli/class-kmp-wpcli.php';
require_once plugin_dir_path(__FILE__) . 'kinsta-mu-plugins/class-kmp.php';
require_once plugin_dir_path(__FILE__) . 'kinsta-mu-plugins/compat/compat.php';
require_once plugin_dir_path(__FILE__) . 'kinsta-mu-plugins/bootstrap.php';
