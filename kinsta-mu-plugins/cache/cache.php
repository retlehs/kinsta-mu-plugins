<?php
/**
 * Kinsta Cache
 *
 * Main file to load Kinsta Cache
 *
 * @package KinstaMUPlugins
 * @subpackage Cache
 * @since 1.0.0
 */

namespace Kinsta;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

define( 'KINSTA_CACHE_DOCS_URL', 'https://kinsta.com/knowledgebase/kinsta-cache-plugin/' );

require plugin_dir_path( __FILE__ ) . 'class-cache-purge.php';
require plugin_dir_path( __FILE__ ) . 'class-litespeed-purge.php';
require plugin_dir_path( __FILE__ ) . 'class-cache-admin.php';
require plugin_dir_path( __FILE__ ) . 'class-cache.php';

/**
 * Determine cache backend type.
 *
 * Priority:
 * 1. KINSTAMU_CACHE_BACKEND constant (explicit override)
 * 2. Auto-detect LiteSpeed server
 * 3. Default to Kinsta
 *
 * @return string 'litespeed' or 'kinsta'
 */
function determine_cache_backend() {
	// Allow explicit override via constant.
	if ( defined( 'KINSTAMU_CACHE_BACKEND' ) ) {
		$backend = strtolower( KINSTAMU_CACHE_BACKEND );
		if ( in_array( $backend, array( 'litespeed', 'kinsta' ), true ) ) {
			return $backend;
		}
	}

	// Auto-detect LiteSpeed server.
	if ( LiteSpeed_Purge::is_litespeed_server() ) {
		return 'litespeed';
	}

	// Default to Kinsta.
	return 'kinsta';
}

$cache_backend = determine_cache_backend();

$config = array(
	'option_name'    => 'kinsta-cache-settings',
	'cache_backend'  => $cache_backend,
	'immediate_path' => 'https://localhost/kinsta-clear-cache/v2/immediate',
	'throttled_path' => 'https://localhost/kinsta-clear-cache/v2/throttled',
);

$default_settings = array(
	'version' => '2.0',
	'options' => array(
		'additional_paths' => array(
			'group' => array(),
			'single' => array(),
		),
	),
	'rules' => array(),
);

$kinsta_cache = new Cache( $config, $default_settings );

/**
 * Backward compatible, WP Rocket plugin's 3.0.1 version caused fatal error without this.
 */
$KinstaCache = $kinsta_cache; // phpcs:ignore
