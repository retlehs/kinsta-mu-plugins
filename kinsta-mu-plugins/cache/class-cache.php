<?php
/**
 * Cache classes
 *
 * This module allows users to fine tune their cache clearing settings and
 * initiates cache clearing when necessary
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
 * Cache class
 *
 * Offers users cache settings and initiates full page and object cache
 * clearing
 *
 * @since 1.0.0
 */
class Cache {

	/**
	 * Cache_Admin instance
	 *
	 * @var Cache_Admin
	 */
	public $kinsta_cache_admin;

	/**
	 * Cache_Purge instance
	 *
	 * @var Cache_Purge
	 */
	public $kinsta_cache_purge;

	/**
	 * Backward compatible Cache_Purge instance
	 * WP Rocket plugin's 3.0.1 version caused fatal error without this
	 *
	 * @var Cache_Purge
	 */
	public $KinstaCachePurge; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.MemberNotSnakeCase

	/**
	 * The cache configuration
	 *
	 * @see ./cache.php
	 * @var array
	 */
	public $config;

	/**
	 * The cache settings
	 *
	 * @see Kinsta/Cache()->set_settings()
	 * @var array
	 */
	public $settings;

	/**
	 * The cache default settings
	 *
	 * @see ./cache.php
	 * @var array
	 */
	public $default_settings;

	/**
	 * Whether the Object cache is enabled
	 *
	 * @var bool
	 */
	public $has_object_cache;

	/**
	 * Class constructor
	 *
	 * @param array $config           The cache configuration.
	 * @param array $default_settings The cache default settings.
	 */
	public function __construct( $config, $default_settings ) {

		$this->config = $config;
		$this->default_settings = $default_settings;
		$this->set_settings();
		$this->set_has_object_cache();

		add_action( 'init', array( $this, 'init_cache' ), 5 );

		add_action( 'wp_ajax_kinsta_clear_cache_all', array( $this, 'action_kinsta_clear_cache_all' ) );
		add_action( 'wp_ajax_kinsta_clear_cache_full_page', array( $this, 'action_kinsta_clear_cache_full_page' ) );
		add_action( 'wp_ajax_kinsta_clear_cache_object', array( $this, 'action_kinsta_clear_cache_object' ) );
		add_action( 'admin_notices', array( $this, 'cleared_cache_notice' ) );

		add_action( 'wp_ajax_kinsta_save_custom_path', array( $this, 'action_kinsta_save_custom_path' ) );
		add_action( 'wp_ajax_kinsta_remove_custom_path', array( $this, 'action_kinsta_remove_custom_path' ) );

		// Removing other cache systems.
		add_filter( 'do_rocket_generate_caching_files', '__return_false', 999 ); // Disable WP rocket caching.
	}

	/**
	 * Init the Caching when the WP is initialised, this is to ensure that the classes, global variables and WordPress core functions are ready.
	 *
	 * @since 2.0.16
	 *
	 * @return void
	 */
	public function init_cache() {

		$this->kinsta_cache_purge = new Cache_Purge( $this );
		$this->kinsta_cache_admin = new Cache_Admin( $this );

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
		$this->KinstaCachePurge = $this->kinsta_cache_purge;

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
	 * [cleared_cache_notice description]
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function cleared_cache_notice() {
		if ( ! empty( $_GET['kinsta-cache-cleared'] ) && 'true' == $_GET['kinsta-cache-cleared'] ) : // WPCS: CSRF ok, loose comparison ok.
			?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Cache cleared successfully', 'kinsta-mu-plugins' ); ?></p>
		</div>
			<?php
		endif;
	}

	/**
	 * Set settings
	 *
	 * @since 1.0.0
	 */
	public function set_settings() {

		$settings = array();
		if ( ! empty( $this->default_settings ) && ! empty( $this->config['option_name'] ) ) {

			// Get settings from database.
			$settings = get_option( $this->config['option_name'] );

			// If there are no settings yet, save the default ones.
			if ( empty( $settings ) ) {
				$settings = $this->default_settings;
				update_option( $this->config['option_name'], $this->default_settings );
			}
		}

		// If there has been a version change scan settings for changes.
		if ( empty( $settings['version'] ) || empty( $this->default_settings['version'] ) || $settings['version'] != $this->default_settings['version'] ) { // WPCS: loose comparison ok.
			foreach ( $this->default_settings['rules'] as $group => $rules ) {
				// If there is a new rule group add it with the default values.
				if ( ! isset( $settings['rules'][ $group ] ) ) {
					$settings['rules'][ $group ] = $rules;
				}

				// If there are new settings within groups add them with the default value.
				foreach ( $rules as $name => $value ) {
					if ( ! isset( $settings['rules'][ $group ][ $name ] ) ) {
						$settings['rules'][ $group ][ $name ] = $this->default_settings['rules'][ $group ][ $name ];
					}
				}
			}

			// Add the new version to the settings.
			$settings['version'] = $this->default_settings['version'];

			// Add options.
			$settings['options'] = $this->default_settings['options'];

			// Save the modified settings.
			update_option( $this->config['option_name'], $settings );
		}

		$this->settings = $settings;
	}

	/**
	 * Save plugin options to the database
	 *
	 * @return void
	 */
	public function save_plugin_options() {
		if ( ! isset( $_POST['kinsta_nonce'] ) || ! wp_verify_nonce( $_POST['kinsta_nonce'], 'save_plugin_options' ) ) {
			die();
		}

		$new_rules = $_POST['rules'];

		foreach ( $this->default_settings['rules'] as $group => $data ) {
			foreach ( $data as $rule => $value ) {
				if ( empty( $new_rules[ $group ][ $rule ] ) ) {
					$new_rules[ $group ][ $rule ] = false;
				} else {
					$new_rules[ $group ][ $rule ] = true;
				}
			}
		}

		$this->settings['rules'] = $new_rules;

		$new_options = $_POST['options'];

		foreach ( $this->default_settings['options'] as $option => $value ) {
			if ( ! isset( $new_options[ $option ] ) ) {
				$new_options[ $option ] = false;
			} elseif ( 'on' === $new_options[ $option ] ) {
				$new_options[ $option ] = true;
			}
		}

		$this->settings['options'] = $new_options;

		update_option( $this->config['option_name'], $this->settings );
	}

	/**
	 * AJAX Action to clear all cache
	 *
	 * @return void
	 */
	public function action_kinsta_clear_cache_all() {

		check_ajax_referer( 'kinsta-clear-cache-all', 'kinsta_nonce' );
		$this->kinsta_cache_purge->purge_complete_caches();
		if ( isset( $_GET ) && isset( $_GET['source'] ) && 'adminbar' == $_GET['source'] ) { // WPCS: loose comparison ok.
			header( 'Location: ' . add_query_arg( 'kinsta-cache-cleared', 'true', $_SERVER['HTTP_REFERER'] ) );
		}
		die();
	}

	/**
	 * AJAX action to clear page cache
	 *
	 * @return void
	 */
	public function action_kinsta_clear_cache_full_page() {

		check_ajax_referer( 'kinsta-clear-cache-full-page', 'kinsta_nonce' );
		$this->kinsta_cache_purge->purge_complete_full_page_cache();
		if ( isset( $_GET ) && isset( $_GET['source'] ) && 'adminbar' == $_GET['source'] ) { // WPCS: CSRF ok, loose comparison ok.
			header( 'Location: ' . add_query_arg( 'kinsta-cache-cleared', 'true', $_SERVER['HTTP_REFERER'] ) );
		}
		die();
	}

	/**
	 * AJAX action to clear Object Cache
	 *
	 * @return void
	 */
	public function action_kinsta_clear_cache_object() {

		check_ajax_referer( 'kinsta-clear-cache-object', 'kinsta_nonce' );
		$this->kinsta_cache_purge->purge_complete_object_cache();
		if ( 'adminbar' == $_GET['source'] ) { // WPCS: CSRF ok, loose comparison ok.
			header( 'Location: ' . add_query_arg( 'kinsta-cache-cleared', 'true', $_SERVER['HTTP_REFERER'] ) );
		}
		die();
	}

	/**
	 * AJAX Action to save custom path
	 *
	 * @return void
	 */
	public function action_kinsta_save_custom_path() {

		if ( ! isset( $_POST['kinsta_nonce'] ) || ! wp_verify_nonce( $_POST['kinsta_nonce'], 'save_plugin_options' ) ) {
			die();
		}

		$paths = get_option( 'kinsta-cache-additional-paths' );
		if ( empty( $paths ) ) {
			$paths = array();
		}

		$paths[] = array(
			'path' => $_POST['path'],
			'type' => $_POST['type'],
		);

		$paths = array_values( $paths );
		update_option( 'kinsta-cache-additional-paths', $paths );
		die();
	}

	/**
	 * AJAX action to remove custom path
	 *
	 * @return void
	 */
	public function action_kinsta_remove_custom_path() {

		if ( ! isset( $_POST['kinsta_nonce'] ) || ! wp_verify_nonce( $_POST['kinsta_nonce'], 'save_plugin_options' ) ) {
			die();
		}

		$paths = get_option( 'kinsta-cache-additional-paths' );

		if ( ! empty( $paths[ $_POST['index'] ] ) ) {
			unset( $paths[ $_POST['index'] ] );
		}

		if ( count( $paths ) === 0 ) {
			delete_option( 'kinsta-cache-additional-paths' );
		} else {
			$paths = array_values( $paths );
			update_option( 'kinsta-cache-additional-paths', $paths );
		}
		die();
	}
}
