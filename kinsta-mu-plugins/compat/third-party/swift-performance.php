<?php
/**
 * File to host codes to handle compatibility with the Swift Performance plugin.
 *
 * @see https://swteplugins.com/product/swift-performance/
 * @package KinstaMUPlugins/Compat
 */

namespace Kinsta\Compat;

if ( ! defined( 'SWIFT_PERFORMANCE_DISABLE_CACHE' ) ) {
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Third-party constant owned by the Swift Performance plugin; the name is defined by that plugin and should not be prefixed.
	define( 'SWIFT_PERFORMANCE_DISABLE_CACHE', true ); // Disable live logging.
}

/**
 * Disaplay admin notice for all the users in case of major issues.
 *
 * @return void
 */
function swift_performance_compatibility_admin_notices() {
	// @phpstan-ignore-next-line booleanNot.alwaysFalse -- The constant may have been defined as `false` elsewhere (wp-config.php, another plugin, theme) before this mu-plugin loads, which is the scenario this notice handles.
	if ( ! SWIFT_PERFORMANCE_DISABLE_CACHE ) {
		?>
		<div id="kinsta-banned-plugins-nag" class="notice notice-kinsta notice-error">
			<p>
				<?php
				echo wp_kses(
					__( 'We\'ve detected that the <code>SWIFT_PERFORMANCE_DISABLE_CACHE</code> constant has been set to <code>false</code>. This can cause cache issues for your site. Please remove this constant from your site\'s wp-config.php file or from the plugin or theme file where it has been defined.', 'kinsta-mu-plugins' ),
					array( 'code' => array() )
				);
				?>
			</p>
		</div>
		<?php
	}
}

if ( function_exists( 'add_action' ) ) {
	add_action( 'admin_notices', __NAMESPACE__ . '\\swift_performance_compatibility_admin_notices', PHP_INT_MAX );
}
