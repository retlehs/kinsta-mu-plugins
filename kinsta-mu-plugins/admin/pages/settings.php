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
				<h3>Kinsta Settings</h3>
			</div>
			<div class="kinsta-content-section-body no-grid">
				<p>
					<input type="checkbox" id="disable-cache-autopurge" name="disable-cache-autopurge">
					<label for="disable-cache-autopurge">
						<?php esc_html_e( 'Disable Cache Autopurge', 'kinsta-mu-plugins' ); ?>
					</label>
					<small>Only disable if your site is having trouble importing posts</small>
				</p>
				<p>
					<input type="checkbox" id="clear-amp-cache" name="clear-amp-cache">
					<label for="clear-amp-cache">
						<?php esc_html_e( 'Clear Cache For AMP Pages', 'kinsta-mu-plugins' ); ?>
					</label>
					<small>Enable this to clear AMP cache on page save</small>
				</p>
				<p>
					<input type="checkbox" id="allow-banned-plugins" name="allow-banned-plugins">
					<label for="allow-banned-plugins">
						<?php esc_html_e( 'Allow banned plugins', 'kinsta-mu-plugins' ); ?>
					</label>
					<small>Allow installing plugins that are banned for performance reasons. This might slow down your site!</small>
				</p>
			</div>
		</div>
		
		<?php
		if ( KINSTAMU_WHITELABEL === false ) {
			include plugin_dir_path( __FILE__ ) . 'partials/sidebar-support.php';
		}
		?>
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
