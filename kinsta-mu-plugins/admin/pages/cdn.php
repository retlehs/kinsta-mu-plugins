<?php
/**
 * Kinsta Cache Main Page
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
<div class="kinsta-wrap">
	<div class='kinsta-page-wrapper'>
		<div class='kinsta-content-section'>
			<div class="kinsta-content-section-header">
				<h3>Clear CDN Cache</h3>
			</div>
			<div class="kinsta-content-section-body">
				<p><?php esc_html_e( 'CDN is enabled. All static content (such as images, CSS, and JavaScript files) is loaded through our CDN. We serve all the folders of your website. The limit is 5 GB per file.', 'kinsta-mu-plugins' ); ?></p>
				<button data-nonce='<?php echo esc_attr( wp_create_nonce( 'kinsta-clear-cdn' ) ); ?>' data-action='kinsta_clear_cdn' class='kinsta-button' data-progressText='<?php echo esc_attr( __( 'Clearing CDN...', 'kinsta-mu-plugins' ) ); ?>' data-completedText='<?php echo esc_attr( __( 'CDN Cleared', 'kinsta-mu-plugins' ) ); ?>'><?php esc_html_e( 'Clear CDN', 'kinsta-mu-plugins' ); ?></button>
			</div>
		</div>
		<hr class="kinsta-content-section-split">
		<div class='kinsta-content-section'>
			<div class="kinsta-content-section-header">
				<h3>Manage CDN Settings</h3>
			</div>
			<div class="kinsta-content-section-body">
				<p><?php esc_html_e( 'Manage your CDN settings, including minification and exclusions, on the MyKinsta CDN settings page', 'kinsta-mu-plugins' ); ?></p>
				<a class="kinsta-button" href="https://my.kinsta.com">View CDN Settings</a>
			</div>
		</div>
		
		<?php if ( KINSTAMU_WHITELABEL === false ) {
				include plugin_dir_path( __FILE__ ) . 'partials/sidebar-support.php';
			} ?>
	</div>
</div>
<script>
jQuery(document).on('click', '.kinsta-clear-cdn', function() {
	var element = jQuery(this);
	jQuery.ajax({
		url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
		type: 'post',
		data: {
			kinsta_nonce: element.attr('data-nonce'),
			action: element.attr( 'data-action' )
		}
	});
	return false;
});
</script>
