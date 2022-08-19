<?php
/**
 * Class that customizes WP for better support for Kinsta.
 *
 * @since 2.4.6
 * @package KinstaMUPlugins
 * @subpackage Compat
 */

namespace Kinsta\Compat;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

/**
 * Class that customizes WP for better support for Kinsta.
 */
class WP_Kinsta {

	/**
	 * Class constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		// Is mobile?
		add_filter( 'wp_is_mobile', array( $this, 'detect_if_mobile' ) );
	}

	/**
	 * Whether or not WordPress should be in mobile mode. This means the device could have limited resources and is not based on screen size.
	 *
	 * @param bool $is_mobile Whether or not WordPress is in mobile mode.
	 *
	 * @return bool
	 */
	public function detect_if_mobile( $is_mobile ) {
		if ( ! empty( $_SERVER['KINSTA_CACHE_ZONE'] ) && 'KINSTAWP_MOBILE' === $_SERVER['KINSTA_CACHE_ZONE'] ) {
			$is_mobile = true;
		}
		return $is_mobile;
	}

}
