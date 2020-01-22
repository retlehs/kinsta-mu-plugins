<?php
/**
 * Compat: Functions
 *
 * @package KinstaMUPlugins/Compat
 */

namespace Kinsta\Compat;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

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
