<?php
/**
 * Cache classes.
 *
 * This module allows users to fine tune their cache clearing settings and initiates cache clearing when necessary.
 *
 * @package KinstaMUPlugins
 * @subpackage Cache
 * @since 1.0.0
 */

namespace Kinsta;

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
class Cache {

	/**
	 * Cache_Admin instance.
	 *
	 * @var Cache_Admin
	 */
	public $kinsta_cache_admin;

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
	 * The cache configuration.
	 *
	 * @see ./cache.php
	 * @var array
	 */
	public $config;

	/**
	 * The cache settings.
	 *
	 * @see Kinsta/Cache()->set_settings()
	 * @var array
	 */
	public $settings;

	/**
	 * The cache default settings.
	 *
	 * @see ./cache.php
	 * @var array
	 */
	public $default_settings;

	/**
	 * Whether the Object cache is enabled.
	 *
	 * @var bool
	 */
	public $has_object_cache;

	/**
	 * Class constructor.
	 *
	 * @param array $config           The cache configuration.
	 * @param array $default_settings The cache default settings.
	 */
	public function __construct( $config = false, $default_settings = false ) {
		if ( empty( $config ) || empty( $default_settings ) ) {
			return;
		}

		// Set our class variables.
		$this->config = $config;
		$this->default_settings = $default_settings;
		$this->set_settings();
		$this->set_has_object_cache();

		// Init the cache classes.
		add_action( 'init', array( $this, 'init_cache' ), 5 );

		// Removing other cache systems.
		add_filter( 'do_rocket_generate_caching_files', '__return_false', 999 ); // Disable WP rocket caching.
	}

	/**
	 * Init the Caching when the WP is initialised, this is to ensure that the classes, global variables, and WordPress core functions are ready.
	 *
	 * @since 2.0.16
	 *
	 * @return void
	 */
	public function init_cache() {
		$this->kinsta_cache_purge = new Cache_Purge( $this );
$this->KinstaCachePurge = $this->kinsta_cache_purge; // phpcs:ignore
		$this->kinsta_cache_admin = new Cache_Admin( $this );

		/**
		 * Hook that fires after cache classes are initialized.
		 *
		 * @param Cache $this Instance of the Cache class.
		 */
		do_action( 'kinsta_cache_init', $this );
	}

	/**
	 * Check if the site has Object Caching enabled.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function set_has_object_cache() {
		$this->has_object_cache = false;
		if ( file_exists( WP_CONTENT_DIR . '/object-cache.php' ) ) {
			$this->has_object_cache = true;
		}
	}

	/**
	 * Set settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function set_settings() {
		// Init settings array which will contain data by the end of this function.
		$settings = array();

		// Get settings from database.
		$settings = get_option( $this->config['option_name'] );

		// If there are no settings yet, save the default ones.
		if ( empty( $settings ) ) {
			// Make the settings available to the class.
			$this->settings = $this->default_settings;
			// Save initial settings.
			update_option( $this->config['option_name'], $this->default_settings );
			// Return early as there is nothing to upgrade for initial settings.
			return;
		}

		// If there has been a version change, scan settings for changes.
		if ( empty( $settings['version'] ) || ( ! empty( $settings['version'] ) && version_compare( $settings['version'], $this->default_settings['version'], '!=' ) ) ) {
			foreach ( $this->default_settings['rules'] as $group => $rules ) {
				// If there is a new rule group, add it with the default values.
				if ( ! isset( $settings['rules'][ $group ] ) ) {
					$settings['rules'][ $group ] = $rules;
				}
				// If there are new settings within groups, add them with the default value.
				foreach ( $rules as $name => $value ) {
					if ( ! isset( $settings['rules'][ $group ][ $name ] ) ) {
						$settings['rules'][ $group ][ $name ] = $this->default_settings['rules'][ $group ][ $name ];
					}
				}
			}

			// Add version defined in default settings to settings now that the upgrade is complete.
			$settings['version'] = $this->default_settings['version'];
			// Add modified settings.
			$settings['options'] = $this->default_settings['options'];
			// Save the modified settings.
			update_option( $this->config['option_name'], $settings );
		}

		// Make the settings available to the class.
		$this->settings = $settings;
	}

}
