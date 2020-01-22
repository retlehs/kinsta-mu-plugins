<?php
/**
 * Compat: Security class
 *
 * @package KinstaMUPlugins/Compat
 */

namespace Kinsta\Compat;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

/**
 * Class to handle WordPress-related basic security practices.
 */
class Security {

	/**
	 * A list of security-related config.
	 *
	 * @var array
	 */
	private $security;

	/**
	 * The Constructor.
	 *
	 * @return null
	 */
	public function __construct() {
		$this->security = get_server_var( 'KINSTA_PLUGIN_OP', 'security' );
	}

	/**
	 * Run the security functionity.
	 *
	 * @return void
	 */
	public function run() {

		if ( ! $this->security ) {
			return;
		}

		if ( isset( $this->security['hide_wp_version'] ) && true === (bool) $this->security['hide_wp_version'] ) {
			$this->hide_wp_version();
		}
	}

	/**
	 * Method to hide WordPress version from the site front-end.
	 *
	 * @return void
	 */
	public function hide_wp_version() {

		remove_action( 'wp_head', 'wp_generator' ); // Remove version from head.

		add_action( 'admin_notices', [ __CLASS__, 'hide_wp_version_admin_notice' ], PHP_INT_MAX );

		add_filter( 'the_generator', '__return_empty_string' ); // Remove version from RSS.
		add_filter( 'style_loader_src', [ __CLASS__, 'hide_wp_version_loader_src' ], PHP_INT_MAX );
		add_filter( 'script_loader_src', [ __CLASS__, 'hide_wp_version_loader_src' ], PHP_INT_MAX );
	}

	/**
	 * Remove the version from scripts and styles query string URL.
	 *
	 * @param string $src The source URL of the enqueued style or script.
	 * @return string
	 */
	public static function hide_wp_version_loader_src( $src ) {

		$wp_version = get_bloginfo( 'version' );
		if ( strpos( $src, "ver={$wp_version}" ) ) {

			$src_path = parse_url( $src, PHP_URL_PATH ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
			$src_file = ABSPATH . $src_path;

			$src = remove_query_arg( 'ver', $src );
			if ( file_exists( $src_file ) ) {
				$src_mod  = filemtime( ABSPATH . $src_path );
				$src = add_query_arg( 'ver', $src_mod, $src );
			}
		}

		return $src;
	}

	/**
	 * Display a notice when the WordPress version is hidden.
	 *
	 * @return void
	 */
	public static function hide_wp_version_admin_notice() {

		$screen = get_current_screen();
		if ( 'kinsta-tools' === $screen->parent_base ) :
			?>
		<div class="notice notice-warning">
			<p>
			<?php

			if ( is_whitelabel_enabled() ) {
				$notice = __( 'For your security, the WordPress version has been hidden from your website\'s source code.', 'kinsta-mu-plugins' );
			} else {
				// Translators: %s reference and URL to the Kinsta.
				$notice = sprintf( __( '%s, the WordPress version has been hidden from your website\'s source code.', 'kinsta-mu-plugins' ), '<a href="https://kinsta.com/blog/wordpress-security/#hide-version" target="_blank">' . __( 'For your security', 'kinsta-mu-plugins' ) . '</a>' );
			}

			echo wp_kses(
				$notice,
				[
					'a' => [
						'href' => true,
						'target' => true,
					],
				]
			);
			?>
			</p>
		</div>
			<?php
		endif;
	}

	/**
	 * Retrieve the value security property.
	 *
	 * @return array
	 */
	public function get_security_var() {
		return $this->security;
	}

}
