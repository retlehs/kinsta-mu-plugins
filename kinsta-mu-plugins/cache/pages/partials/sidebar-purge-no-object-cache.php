<?php
/**
 * Template to display sidebar when the site has no object cache enabled
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
	<button data-nonce='<?php echo esc_attr( wp_create_nonce( 'kinsta-clear-cache-all' ) ); ?>' data-action='kinsta_clear_cache_all' class='kinsta-clear-cache kinsta-button kinsta-button__large kinsta-button__full kinsta-loader' data-progressText='<?php echo esc_attr( __( 'Clearing Cache...', 'kinsta-mu-plugins' ) ); ?>' data-completedText='<?php echo esc_attr( __( 'Cache Cleared', 'kinsta-mu-plugins' ) ); ?>'><?php esc_html_e( 'Clear Cache', 'kinsta-mu-plugins' ); ?></button>
</div>
