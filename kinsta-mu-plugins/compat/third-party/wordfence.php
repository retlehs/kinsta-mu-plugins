<?php
/**
 * File to host codes to handle compatibility with the WordFence plugin.
 *
 * @package KinstaMUPlugins/Compat
 */

namespace Kinsta\Compat;

if ( ! defined( 'WORDFENCE_DISABLE_LIVE_TRAFFIC' ) ) {
	define( 'WORDFENCE_DISABLE_LIVE_TRAFFIC', true ); // Disable live logging.
}

/**
 * Disaplay admin notice for all the users in case of major issues.
 *
 * @return void
 */
function wordfence_compatibility_admin_notices() {

	// Check if the WORDFENCE_DISABLE_LIVE_TRAFFIC is set to "false".
	if ( ! WORDFENCE_DISABLE_LIVE_TRAFFIC ) {
		?>
		<div id="kinsta-banned-plugins-nag" class="notice notice-kinsta notice-error">
			<p>
				<?php _e( 'We\'ve detected that the <code>WORDFENCE_DISABLE_LIVE_TRAFFIC</code> constant has been set to <code>false</code>. This can cause significant performance issues for your site. Please remove this constant from your site\'s wp-config.php file or from the plugin or theme file where it has been defined.', 'kinsta-mu-plugins' ); ?>
			</p>
		</div>
		<?php
	}
}
if ( function_exists( 'add_action' ) ) {
	add_action( 'admin_notices', __NAMESPACE__ . '\\wordfence_compatibility_admin_notices', PHP_INT_MAX ); }
