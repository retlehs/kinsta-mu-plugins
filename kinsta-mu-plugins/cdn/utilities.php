<?php
/**
 * CDN: Function Utilities
 *
 * @package KinstaMUPlugins/CDN
 */

namespace Kinsta\CDN;

/**
 * Sanitize the file types to exclude from the CDN.
 *
 * @param string $types Comma-delimited list of file types to exclude.
 * @return array
 */
function sanitize_exclude_types( $types = '' ) {

	$sanitized_types = [];

	if ( ! empty( $types ) ) {
		$sanitized_types = explode( ',', $types );
	}

	if ( is_array( $sanitized_types ) && 0 < count( $sanitized_types ) ) {
		$sanitized_types = array_map(
			function( $type ) {
				$type = trim( $type );
				return '.' !== substr( $type, 0, 1 ) ? ".{$type}" : $type;
			},
			$sanitized_types
		);
	}

	return array_unique( $sanitized_types );
}

/**
 * Function to check if the request happens at the admin screen.
 *
 * @return bool
 */
function is_admin_referred() {

	$admin_url = get_admin_url();
	$referer = isset( $_SERVER ) && isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';

	if ( ! is_string( $referer ) ) {
		return true;
	}

	return ( substr( $referer, 0, strlen( $admin_url ) ) === $admin_url );
}

/**
 * Function to check whether the current request is a WordPress Ajax request.
 *
 * This function is simply replicating `wp_doing_ajax` which is available in WordPress
 * since 4.7.0. It's defined to minimize compatibility issue as some of the site
 * may still be running WordPress older than version 4.7.0.
 *
 * @return bool
 */
function is_doing_ajax() {
	return defined( 'DOING_AJAX' ) && DOING_AJAX;
}

/**
 * Function to check if the current request is a WP REST API request.
 *
 * Case #1: After WP_REST_Request initialisation
 * Case #2: Support "plain" permalink settings
 * Case #3: URL Path begins with wp-json/ (your REST prefix)
 *          Also supports WP installations in subfolders
 *
 * @see https://gist.github.com/matzeeable/dfd82239f48c2fedef25141e48c8dc30
 *
 * @returns bool
 */
function is_rest_api() {
	$prefix = rest_get_url_prefix();

	// phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST || isset( $_GET['rest_route'] ) && strpos( trim( $_GET['rest_route'], '\\/' ), $prefix, 0 ) === 0 ) {
		return true;
	}

	/**
	 * Keep using the native PHP function instead of using `wp_parse_url`.
	 * The `wp_parse_url` is only added in WordPress 4.4.0 and some
	 * client site's may still be using older WordPress version.
	 */
	$rest_url = parse_url( site_url( $prefix ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
	$current_url = parse_url( add_query_arg( [] ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url

	return strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
}

/**
 * Function to check if the WordPress content is rendered with preview mode.
 *
 * @return bool
 */
function is_preview_mode() {
	return array_key_exists( 'preview', $_GET ) && 'true' == $_GET['preview']; // WPCS: loose comparison ok, CSRF.
}
