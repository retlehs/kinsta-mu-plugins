<?php
/**
 * Template to display sidebar when the site has object cache enabled
 *
 * @package KinstaMUPlugins
 * @subpackage Cache
 * @since 1.0.0
 */

namespace Kinsta;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

?>

<div class='kinsta-widget'>
	<div class="kinsta-dropdown kinsta-dropdown__full">
		<button class='kinsta-button kinsta-button__full kinsta-button__large'><?php esc_html_e( 'Clear Caches', 'kinsta-mu-plugins' ); ?></button>
		<div class="kinsta-dropdown-content">
			<button data-nonce='<?php echo esc_attr( wp_create_nonce( 'kinsta-clear-cache-all' ) ); ?>' data-action='kinsta_clear_cache_all' class='kinsta-clear-cache kinsta-button kinsta-button__white kinsta-button__small kinsta-button__full-left kinsta-loader' data-progressText='<?php echo esc_attr( __( 'Clearing Caches...', 'kinsta-mu-plugins' ) ); ?>'  data-completedText='<?php echo esc_attr( __( 'Caches Cleared', 'kinsta-mu-plugins' ) ); ?>'><?php esc_html_e( 'Clear All Caches', 'kinsta-mu-plugins' ); ?></button>

			<button data-nonce='<?php echo esc_attr( wp_create_nonce( 'kinsta-clear-cache-full-page' ) ); ?>' data-action='kinsta_clear_cache_full_page' class='kinsta-clear-cache kinsta-button kinsta-button__white kinsta-button__small kinsta-button__full-left kinsta-loader' data-progressText='<?php echo esc_attr( __( 'Clearing Full Page Cache...', 'kinsta-mu-plugins' ) ); ?>' data-completedText='<?php echo esc_attr( __( 'Cache Cleared', 'kinsta-mu-plugins' ) ); ?>'>
			<?php esc_html_e( 'Clear Full Page Cache Only', 'kinsta-mu-plugins' ); ?></button>

			<button data-nonce='<?php echo esc_attr( wp_create_nonce( 'kinsta-clear-cache-object' ) ); ?>' data-action='kinsta_clear_cache_object' class='kinsta-clear-cache kinsta-button kinsta-button__white kinsta-button__small kinsta-button__full-left kinsta-loader' data-progressText='<?php echo esc_attr( __( 'Clearing Object Cache...', 'kinsta-mu-plugins' ) ); ?>'  data-completedText='<?php echo esc_attr( __( 'Cache Cleared', 'kinsta-mu-plugins' ) ); ?>'><?php esc_html_e( 'Clear Object Cache Only', 'kinsta-mu-plugins' ); ?></button>
		</div>
	</div>
</div>
