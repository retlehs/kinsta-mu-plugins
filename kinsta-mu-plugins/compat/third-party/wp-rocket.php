<?php
/**
 * File to host codes to handle compatibility with the WordFence plugin.
 *
 * @package KinstaMUPlugins/Compat
 */

namespace Kinsta;

use WP_Screen;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

/**
 * Add admin notice for outdated WP Rocket installs.
 */
function wprocket_upgrade_notice() {
	if ( defined( 'KINSTAMU_DISABLE_WPROCKET_NOTICE' ) && KINSTAMU_DISABLE_WPROCKET_NOTICE === true ) {
		return;
	}

	if ( ! function_exists( 'get_current_screen' ) ) {
		return;
	}

	$screen = get_current_screen();

	/**
	 * Make sure to show the notice only on the dashboard, and when the user have
	 * the correct permissions.
	 */
	if (
		! $screen instanceof WP_Screen
		|| 'dashboard' !== $screen->id
		|| ! current_user_can( set_view_role_or_capability() )
	) {
		return;
	}

	$version = get_plugins()['wp-rocket/wp-rocket.php']['Version'] ?? null;

	/**
	 * If the version installed is equal to or greater than 3.10.8,
	 * don't show the notice.
	 */
	if ( ! is_string( $version ) || ! version_compare( $version, '3.10.8', '<' ) ) {
		return;
	}

	?>
	<div class="notice notice-kinsta notice-warning">
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
 * Disable WP Rocket caching.
 *
 * @see https://github.com/wp-media/wp-rocket/blob/develop/inc/classes/Buffer/class-cache.php#L514-L523
 */
add_filter( 'do_rocket_generate_caching_files', '__return_false', PHP_INT_MAX );
add_action( 'admin_notices', __NAMESPACE__ . '\\wprocket_upgrade_notice', PHP_INT_MAX );
