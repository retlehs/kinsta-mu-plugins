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
	 * The kmp class.
	 *
	 * @var \Kinsta\KMP
	 */
	public $kmp;

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
	 * @param \Kinsta\KMP $kmp The KMP object.
	 */
	public function __construct( \Kinsta\KMP $kmp ) {
		// Set our class variables.
		$this->kmp = $kmp;
		$this->config = array(
			'option_name' => 'kinsta-cache-settings',
			'immediate_path' => 'https://localhost/kinsta-clear-cache/v2/immediate',
			'throttled_path' => 'https://localhost/kinsta-clear-cache/v2/throttled',
		);
		$this->default_settings = array(
			'version' => '2.0',
			'options' => array(
				'additional_paths' => array(
					'group' => array(),
					'single' => array(),
				),
			),
			'rules' => array(),
		);
        $this->hook();
		$this->set_settings();
		$this->set_has_object_cache();
	}

    public function hook(): void
    {
        add_filter('default_option_' . $this->config['option_name'], function () {
            return $this->default_settings;
        });
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
		if ( empty( $settings ) || gettype( $settings ) !== 'object' ) {
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
