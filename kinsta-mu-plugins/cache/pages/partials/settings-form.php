<?php
/**
 * Kinsta Cache Settings Form Page
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
<form method="post">
	<div class='kinsta-box'>
		<fieldset class='mb22'>
			<legend class='kinsta-box-title-bar kinsta-box-title-bar__small mb22'><h3><?php esc_html_e( 'Custom URLs To Purge', 'kinsta-mu-plugins' ); ?></h3></legend>
			<p>
			<?php
			if ( KINSTAMU_WHITELABEL === false ) :
				// translators: %s Kinsta Cache URL.
				$message_format = __( 'You can add custom paths to purge whenever your site is updated. Please see our %s for more information on how to use this feature effectively.', 'kinsta-mu-plugins' );

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
					'<a href="' . esc_url( KINSTA_CACHE_DOCS_URL ) . '" target="_blank">' . esc_html__( 'documentation', 'kinsta-mu-plugins' ) . '</a>'
				);
			else :
				echo esc_html_e( 'You can add custom paths to purge whenever your site is updated.', 'kinsta-mu-plugins' );
			endif;
			?>
			</p>

			<div id="custom-url-form">
				<h3>Add A Custom URL</h3>
				<div id="custom-url-form-fields">
					<?php
					Shared::kinsta_select_field(
						'custom-url-type',
						'custom-url-type',
						'single',
						'',
						false,
						array(
							'single' => 'Single Path',
							'group' => 'Group Path',
						)
					);
					$prefix_title = home_url( '/' );
					$prefix_scheme = ( strpos( home_url(), 'https://' ) !== false ) ? 'https' : 'http';
					$prefix_length = strlen( $prefix_title );
					$prefix_islong = ( $prefix_length > 45 ) ? ' isLong' : '';

					$prefix_display = ( $prefix_length > 45 ) ? substr( $prefix_title, 0, 20 ) . '...' . substr( $prefix_title, -20 ) : $prefix_title;

					$prefix_extra_class = $prefix_islong;
					?>
					<span onClick="jQuery('#addURLField').focus()" class="prefix<?php echo esc_attr( $prefix_extra_class ); ?>" title="<?php echo esc_attr( $prefix_title ); ?>"><?php echo esc_attr( $prefix_display ); ?></span><input id="addURLField" type="text" placeholder="Enter a Path" />
					<input id="addURLSubmit" type="submit" value="Add URL">
				</div>
			</div>

			<?php
				$additional_paths = get_option( 'kinsta-cache-additional-paths' );
				$display = ( empty( $additional_paths ) ) ? 'none' : 'table';
				echo '<table id="additionalURLTable" class="kinsta-table" style="margin-top:22px; display:' . esc_attr( $display ) . '">';
				echo '<thead><tr><th>Type</th><th>Path</th><th></th></tr></thead>';
				echo '<tbody>';

			if ( ! empty( $additional_paths ) ) {
				foreach ( $additional_paths as $additional_path ) {
					echo '<tr>';
					echo '<td>' . esc_html( $additional_path['type'] ) . '</td>';
					echo '<td>/' . esc_html( $additional_path['path'] ) . '</td>';
					echo '<td><a class="removePath" href="#">remove</a></td>';
					echo '</tr>';
				}
			}
				echo '</tbody>';
				echo '</table>';
			?>
		</fieldset>
	</div>
	<?php wp_nonce_field( 'save_plugin_options', 'kinsta_nonce' ); ?>
	<input type="hidden" name="action" value='save_plugin_options'>

	<script type="text/javascript">
		jQuery(document).on('click', '#addURLSubmit', function() {
			var path = jQuery('#addURLField').val();
			var type = jQuery('select[name="custom-url-type"]').val()
			if( path === '' || path === null || typeof path === 'undefined' ) {
				return false
			}
			jQuery.ajax({
				url: ajaxurl,
				method: 'post',
				data: {
					action: 'kinsta_save_custom_path',
					kinsta_nonce: jQuery('#kinsta_nonce').val(),
					path: path,
					type: type
				},
				success: function( result ) {
					jQuery('#addURLField').val('')
					if( jQuery('#additionalURLTable tbody tr').length === 0 ) {
						jQuery('#additionalURLTable').show();
					}

					var row = jQuery('<tr></tr>');
					row.append('<td>'+type+'</td>')
					row.append('<td>/'+path.replace(/</g,"&lt;").replace(/>/g,"&gt;")+'</td>')
					row.append('<td><a class="removePath" href="#">remove</a></td>')
					jQuery('#additionalURLTable').append(row)
				}
			})
			return false;
		})

		jQuery(document).on('click', '.removePath', function() {
			var row = jQuery(this).parents('tr:first')
			var index = row.index()
			jQuery.ajax({
				url: ajaxurl,
				method: 'post',
				data: {
					action: 'kinsta_remove_custom_path',
					kinsta_nonce: jQuery('#kinsta_nonce').val(),
					index: index,
				},
				success: function( result ) {
					row.fadeOut( function() {
						row.remove();
						if( jQuery('#additionalURLTable tbody tr').length === 0 ) {
							jQuery('#additionalURLTable').hide();
						}
					})
				}
			})
		})
	</script>
</form>
