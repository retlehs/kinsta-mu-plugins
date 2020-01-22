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
require plugin_dir_path( __FILE__ ) . 'class-cache-admin.php';
require plugin_dir_path( __FILE__ ) . 'class-cache.php';

$config = array(
	'option_name' => 'kinsta-cache-settings',
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
);

$kinsta_cache = new Cache( $config, $default_settings );

/**
 * Backward compatible, WP Rocket plugin's 3.0.1 version caused fatal error without this.
 */
$KinstaCache = $kinsta_cache; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCase
