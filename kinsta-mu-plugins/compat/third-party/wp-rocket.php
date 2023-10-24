<?php
/**
 * File to host codes to handle compatibility with the WordFence plugin.
 *
 * @package KinstaMUPlugins/Compat
 */

namespace Kinsta;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

/**
 * Add admin notice for outdated WP Rocket installs
 */
function wprocket_upgrade_notice() {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	// Make sure the get_current_screen function exists.
	if ( ! function_exists( 'get_current_screen' ) ) {
		require_once ABSPATH . 'wp-admin/includes/screen.php';
	}
	if ( get_current_screen()->id !== 'dashboard' ) {
		return;
	}
	?>
	<div id="kinsta-banned-plugins-nag" class="notice notice-kinsta notice-error is-dismissible">
		<p>
		<?php
			$message_format = __( 'Your WP Rocket version is out-of-date and not fully compatible with Kinsta. %s', 'kinsta-mu-plugins' );

			echo sprintf(
				wp_kses(
					$message_format,
					array(
						'a' => array(
							'href' => true,
							'target' => '_blank',
						),
					)
				),
				'<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">' . esc_html__( 'Please update WP Rocket on the Plugins page', 'kinsta-mu-plugins' ) . '</a>'
			);
		?>
		</p>
	</div>
	<?php
}
/**
 * Check WP Rocket version
 */
function check_wp_rocket_version() {
	if ( defined( 'KINSTAMU_DISABLE_WPROCKET_NOTICE' ) && KINSTAMU_DISABLE_WPROCKET_NOTICE === true ) {
		return false;
	}
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if ( array_key_exists( 'wp-rocket/wp-rocket.php', get_plugins() ) ) {
		if ( version_compare( get_plugins()['wp-rocket/wp-rocket.php']['Version'], '3.10.8', '<' ) ) {
			if ( function_exists( 'add_action' ) && current_user_can( set_view_role_or_capability() ) ) {
				add_action( 'admin_notices', __NAMESPACE__ . '\\wprocket_upgrade_notice', PHP_INT_MAX );
			}
		}
		add_filter( 'do_rocket_generate_caching_files', '__return_false', 999 ); // Disable WP rocket caching.
	}
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\check_wp_rocket_version' );
