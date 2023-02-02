<?php
/**
 * Compat: Functions
 *
 * @package KinstaMUPlugins/Compat
 */

namespace Kinsta;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

/**
 * Add KINSTA_CACHE_ZONE detection to WP Mobile detection
 *
 * @param bool $is_mobile The passed value from wp_is_mobile.
 * @return bool
 */
function kinsta_is_mobile( $is_mobile ) {
	if ( ! empty( $_SERVER['KINSTA_CACHE_ZONE'] ) && 'KINSTAWP_MOBILE' === $_SERVER['KINSTA_CACHE_ZONE'] ) {
		$is_mobile = true;
	}
	return $is_mobile;
}

add_filter( 'wp_is_mobile', __NAMESPACE__ . '\\kinsta_is_mobile' );

/**
 * Get the variable from the $_SERVER global.
 *
 * @param string $server_key A key in $_SERVER global variable.
 * @param string $response_key The first level of key from the $_SERVER response.
 * @return mixed
 */
function get_server_var( $server_key, $response_key ) {

	$response = null;
	if ( isset( $_SERVER ) && isset( $_SERVER[ $server_key ] ) ) {
		$response = json_decode( $_SERVER[ $server_key ], true );
	}

	return isset( $response[ $response_key ] ) ? $response[ $response_key ] : $response;
}

/**
 * A helper function to check if the Whitelable is enabled.
 *
 * @return bool
 */
function is_whitelabel_enabled() {
	return defined( 'KINSTAMU_WHITELABEL' ) && true === KINSTAMU_WHITELABEL;
}

/**
 * Sets the required capability to view and use the cache purging options.
 *
 * @return  string the required capability
 */
function set_view_role_or_capability() {
	if ( defined( 'KINSTAMU_ROLE' ) && is_string( KINSTAMU_ROLE ) ) {
		return esc_attr( KINSTAMU_ROLE );
	}
	return 'manage_options';
}
