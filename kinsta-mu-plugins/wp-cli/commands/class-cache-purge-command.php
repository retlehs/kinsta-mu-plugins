<?php
/**
 * Compat: WP_CLI class
 *
 * @package KinstaMUPlugins/Compat
 */

namespace Kinsta\WP_CLI;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

use WP_CLI;
use WP_CLI_Command;

use Kinsta\Cache_Purge;

/**
 * Class to register to register a custom Kinsta WP-CLI command .
 */
class Cache_Purge_Command extends WP_CLI_Command {

	/**
	 * The Kinsta\Cache_Purge instance.
	 *
	 * @var Cache_Purge
	 */
	private $kinsta_cache_purge;

	/**
	 * The Constructor.
	 *
	 * @param Cache_Purge $kinsta_cache_purge the kinsta_cache_purge class.
	 */
	public function __construct( Cache_Purge $kinsta_cache_purge ) {
		$this->kinsta_cache_purge = $kinsta_cache_purge;
	}

	/**
	 * Clear all Kinsta Cache.
	 *
	 * ## EXAMPLES
	 *
	 *     # Clear all (Kinsta) page cache on the site.
	 *     $ wp kinsta cache purge
	 *     Success: Cache has been cleared.
	 *
	 * ## OPTIONS
	 *
	 * [--object]
	 * : Whether to clear the object cache instaed.
	 *
	 * @uses absint Convert a value to non-negative integer. Introduced since WordPress 2.5.0.
	 * @uses is_wp_error Check whether variable is a WordPress Error. Introduced since WordPress 2.1.0
	 * @uses wp_remote_retrieve_body Retrieve only the body from the raw response. Introduced since WordPress 2.7.0
	 * @uses wp_remote_retrieve_response_code Retrieve only the body from the raw response. Introduced since WordPress 2.7.0
	 * @uses wp_remote_retrieve_response_message Retrieve only the response message from the raw response. Introduced since WordPress 2.7.0
	 *
	 * @param array $args The command arguments.
	 * @param array $assoc_args The command associative arguments e.g. --object, and --all.
	 * @return void
	 */
	public function __invoke( $args, $assoc_args ) {

		if ( isset( $assoc_args['object'] ) ) {
			$this->purge_object_cache();
		} else {
			$this->purge_site_cache();
		}
	}

	/**
	 * Purge all the page cache on the site.
	 *
	 * @return void
	 **/
	private function purge_site_cache() {

		$response = $this->kinsta_cache_purge->purge_complete_site_cache();

		if ( is_wp_error( $response ) ) {
			WP_CLI::error( $response->get_error_message() );
			return;
		}

		$body = wp_remote_retrieve_body( $response );
		$code = wp_remote_retrieve_response_code( $response );
		$message = wp_remote_retrieve_response_message( $response );

		if ( 200 === absint( $code ) && 'Cache has been cleared.' === trim( $body ) ) {
			WP_CLI::success( trim( $body ) );
			return;
		}

		WP_CLI::error( "{$code} {$message}" );
	}

	/**
	 * Purge the object cache.
	 *
	 * @return void
	 */
	private function purge_object_cache() {

		$response = $this->kinsta_cache_purge->purge_complete_object_cache();

		if ( true === $response ) {
			WP_CLI::success( __( 'The Object Cache was purged.', 'kinsta-mu-plugins' ) );
		} else {
			WP_CLI::error( __( 'Something went wrong! The Object Cache was not purged.', 'kinsta-mu-plugins' ) );
		}
	}
}
