<?php
/**
 * Main KMP class.
 *
 * This class is the global class for all the KMP classes and functionality
 *
 * @package KinstaMUPlugins
 * @since 3.0.0
 */

namespace Kinsta;

use Kinsta\KMP\Cache\Autopurge;
use Kinsta\KMP\Cache\Autopurge\ACFController;
use Kinsta\KMP\Cache\Autopurge\WPOptionController;
use Kinsta\KMP\Cache\Autopurge\WPPostController;
use Kinsta\KMP\Cache\Autopurge\WPThemeController;
use Kinsta\KMP\Cache\Autopurge\WPThemeHeaderController;
use Kinsta\KMP\Cache\Autopurge\WPThemeWidgetController;
use Kinsta\KMP\Cache\Autopurge\WooCommerceController;
use Kinsta\KMP\Compat\Elementor;
use Kinsta\KMP\Plugin;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

/**
 * Cache class.
 *
 * Offers users cache settings and initiates full page and object cache clearing.
 *
 * @since 1.0.0
 */
class KMP {

	/**
	 * KMP_Admin instance.
	 *
	 * @var KMP_Admin
	 */
	public $kmp_admin;

	/**
	 * Cache instance.
	 *
	 * @var Cache
	 */
	public $kinsta_cache;

	/**
	 * Cache_Purge instance.
	 *
	 * @var Cache_Purge
	 */
	public $kinsta_cache_purge;

	/**
	 * Backward compatible Cache_Purge instance.
	 * WP Rocket version 3.0.1 caused fatal error without this.
	 *
	 * @var Cache_Purge
	 */
	public $KinstaCachePurge; // phpcs:ignore

	/**
	 * Banned Plugins instance
	 *
	 * @var Banned_Plugins
	 */
	public $banned_plugins;

	/**
	 * Banned Plugins instance
	 *
	 * @var WP_CLI
	 */
	public $wp_cli;

	/**
	 * Banned Plugins instance
	 *
	 * @var string
	 */
	public $cdn_cacheid;


	/**
	 * Class constructor.
	 */
	public function __construct() {
		// Init the cache classes.
		add_action( 'init', array( $this, 'init_kmp' ), 5 );
	}

	/**
	 * Init the classes when the WP is initialised, this is to ensure that the classes, global variables, and WordPress core functions are ready.
	 *
	 * @since 2.0.16
	 *
	 * @return void
	 */
	public function init_kmp() {
		// This doesn't work right now because we don't have the cacheid available yet.
		$this->cdn_cacheid = '';
		$this->kinsta_cache = new Cache( $this );
		$this->kinsta_cache_purge = new Cache_Purge( $this );
		$this->KinstaCachePurge = $this->kinsta_cache_purge; // phpcs:ignore
		$this->kmp_admin = new KMP_Admin( $this );
		$this->banned_plugins = new Banned_Plugins();

        /**
         * Initialize the autopurge manager and orchastrator.
         */
        $autopurge = new Autopurge();
        $autopurge->hook();
        $autopurge->add(
            new WPPostController($this),
            new WPOptionController($this),
            new WPThemeController($this),
            new WPThemeHeaderController($this),
            new WPThemeWidgetController($this),
            new WooCommerceController($this),
            new ACFController($this),
            new Elementor($this),
        );

		$this->wp_cli = new KMP_WPCLI( $this, $autopurge );
	}

	/**
	 * Sets the required capability to view and use the cache purging options.
	 *
	 * @return  string the required capability
	 */
	public function is_cdn_enabled() {
		return '' !== $this->cdn_cacheid;
	}
}
