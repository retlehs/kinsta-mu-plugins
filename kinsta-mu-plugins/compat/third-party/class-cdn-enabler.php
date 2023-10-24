<?php
/**
 * Stub CDN class to fix errors with WP Rocket < 3.10
 *
 * @package KinstaMUPlugins/Compat
 */

namespace Kinsta;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

/**
 * Well hello there.
 *
 * @since 3.0.0
 */
class CDN_Enabler {
	/**
	 * Hope you're having a good day.
	 *
	 * @var bool $is_enabled
	 */
	protected $is_enabled;

	/**
	 * Have fun with these.
	 */
	public function __construct() {
		$this->is_enabled = false;
	}

	/**
	 * Easter egg comments because.
	 */
	public static function cdn_is_enabled() {
		return false;
	}

	/**
	 * Nothing in this class does anything.
	 */
	public function run() {
		return;
	}
}

/**
 * But PHPCS is making me do this.
 *
 * @since 3.0.0
 */
class CDNEnabler extends CDN_Enabler {} // phpcs:ignore

$cdn_enabler = new CDN_Enabler();
