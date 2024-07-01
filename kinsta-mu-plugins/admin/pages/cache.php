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
				<h3><?php esc_html_e( 'Cache Control', 'kinsta-mu-plugins' ); ?></h3>
			</div>
			<div class="kinsta-content-section-body">
				<p><?php esc_html_e( 'Your site uses our full page and object caching technology to load lightning fast. We purge single pages and key pages such as the home page immediately and impose a minimal throttle time on archive pages. This ensures high availability at all times.', 'kinsta-mu-plugins' ); ?></p>
				<a class="button button-primary kinsta-button" href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=kinsta-tools&clear-cache=kinsta-clear-all-cache' ), 'kinsta-clear-cache-admin-bar', 'kinsta_nonce' ); ?>"><?php esc_html_e( 'Clear All Caches', 'kinsta-mu-plugins' ); ?></a>
			</div>
		</div>
		<hr class="kinsta-content-section-split">
		<div class='kinsta-content-section'>
			<div class="kinsta-content-section-header">
				<h3><?php esc_html_e( 'Site Caching', 'kinsta-mu-plugins' ); ?></h3>
			</div>
			<div class="kinsta-content-section-body">
				<p><?php esc_html_e( 'Cache makes your site load faster by storing site data. Clear it to make sure your site shows the most recent version.', 'kinsta-mu-plugins' ); ?></p>
				<a class="button button-primary kinsta-button" href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=kinsta-tools&clear-cache=kinsta-clear-site-cache' ), 'kinsta-clear-cache-admin-bar', 'kinsta_nonce' ); ?>"><?php esc_html_e( 'Clear Site Cache', 'kinsta-mu-plugins' ); ?></a>
			</div>
		</div>
		<hr class="kinsta-content-section-split">
		<div class='kinsta-content-section'>
			<div class="kinsta-content-section-header">
				<h3><?php esc_html_e( 'Object Caching', 'kinsta-mu-plugins' ); ?></h3>
			</div>
			<div class="kinsta-content-section-body">
				<p><?php esc_html_e( 'The WordPress Object Cache is used to save on trips to the database. The Object Cache stores all of the cache data to memory and makes the cache contents available by using a key, which is used to name and later retrieve the cache contents.', 'kinsta-mu-plugins' ); ?></p>
				<a class="button button-primary kinsta-button" href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=kinsta-tools&clear-cache=kinsta-clear-object-cache' ), 'kinsta-clear-cache-admin-bar', 'kinsta_nonce' ); ?>"><?php esc_html_e( 'Clear Object Cache', 'kinsta-mu-plugins' ); ?></a>
			</div>
		</div>
		<hr class="kinsta-content-section-split">
		<div class='kinsta-content-section'>
			<div class="kinsta-content-section-header">
				<h3>CDN Caching</h3>
			</div>
			<div class="kinsta-content-section-body">
				<p><?php esc_html_e( 'All static content (such as images, CSS, and JavaScript files) is loaded through our CDN, if it is enabled. We serve all the folders of your website. The limit is 5 GB per file.', 'kinsta-mu-plugins' ); ?></p>
				<a class="button button-primary kinsta-button" href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=kinsta-tools&clear-cache=kinsta-clear-cdn-cache' ), 'kinsta-clear-cache-admin-bar', 'kinsta_nonce' ); ?>"><?php esc_html_e( 'Clear CDN Cache', 'kinsta-mu-plugins' ); ?></a>
			</div>
		</div>
		<hr class="kinsta-content-section-split">
		<div class='kinsta-content-section'>
			<div class="kinsta-content-section-header">
				<h3><?php esc_html_e( 'Custom URLs to purge', 'kinsta-mu-plugins' ); ?></h3>
			</div>
			<div class="kinsta-content-section-body no-grid">
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
							'<a href="' . esc_url( KMP_DOCS_URL ) . '" target="_blank">' . esc_html__( 'documentation', 'kinsta-mu-plugins' ) . '</a>'
						);
					else :
						echo esc_html_e( 'You can add custom paths to purge whenever your site is updated.', 'kinsta-mu-plugins' );
					endif;
					?>
				</p>
				<form class="kinsta-custom-url-form" method="post">
					<h3><?php esc_html_e( 'Add A Custom URL', 'kinsta-mu-plugins' ); ?></h3>
					<div id="kinsta-custom-url-form-fields">
						<div class='kinsta-select-field kinsta-control-container' data-option-name="<?php echo esc_attr( 'custom-url-type' ); ?>">
							<label>
								<select name='custom-url-type' class='kinsta-control'>
									<option value="single">Single Path</option>
									<option value="group">Group Path</option>
								</select>
							</label>
							<input type='hidden' name='kinsta-nonce' value='<?php echo esc_attr( 'kinsta_select_field_' . wp_create_nonce( 'custom-url-type' ) ); ?>'>
						</div>
						<?php
							$prefix_title = home_url( '/' );
							$prefix_scheme = ( strpos( home_url(), 'https://' ) !== false ) ? 'https' : 'http';
							$prefix_length = strlen( $prefix_title );
							$prefix_islong = ( $prefix_length > 45 ) ? ' isLong' : '';

							$prefix_display = ( $prefix_length > 45 ) ? substr( $prefix_title, 0, 20 ) . '...' . substr( $prefix_title, -20 ) : $prefix_title;

							$prefix_extra_class = $prefix_islong;
						?>
						<span onClick="jQuery('#addURLField').focus()" class="prefix<?php echo esc_attr( $prefix_extra_class ); ?>" title="<?php echo esc_attr( $prefix_title ); ?>"><?php echo esc_attr( $prefix_display ); ?></span><input id="addURLField" type="text" placeholder="Enter a Path" />
						<input id="addURLSubmit" type="submit" class="button button-primary kinsta-button" value="Add URL">
					</div>
					<?php
						$additional_paths = get_option( 'kinsta-cache-additional-paths' );
						echo '<table id="additionalURLTable" class="kinsta-table">';
						echo '<thead><tr><th>Type</th><th>Path</th><th>Action</th></tr></thead>';
						echo '<tbody>';

					if ( ! empty( $additional_paths ) ) {
						foreach ( $additional_paths as $additional_path ) {
							echo '<tr>';
							echo '<td>' . esc_html( $additional_path['type'] ) . '</td>';
							echo '<td>/' . esc_html( $additional_path['path'] ) . '</td>';
							echo '<td><a class="removePath" href="#">Remove</a></td>';
							echo '</tr>';
						}
					}
						echo '</tbody>';
						echo '</table>';
					?>
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
									var row = jQuery('<tr></tr>');
									row.append('<td>'+type+'</td>')
									row.append('<td>/'+path.replace(/</g,"&lt;").replace(/>/g,"&gt;")+'</td>')
									row.append('<td><a class="removePath" href="#">Remove</a></td>')
									jQuery('#additionalURLTable').append(row)
								}
							})
							return false;
						})

						jQuery(document).on('click', '.removePath', function(e) {
							e.preventDefault();
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
									})
								}
							})
						})
					</script>
				</form>
			</div>
		</div>
		<hr class="kinsta-content-section-split">
		<div class='kinsta-content-section'>
			<div class="kinsta-content-section-header">
				<h3><?php esc_html_e( 'Cache Autopurge', 'kinsta-mu-plugins' ); ?></h3>
			</div>
			<div class="kinsta-content-section-body">
				<p><?php esc_html_e( 'We purge the full page cache on every page and post update. If you are importing posts, you can disable the autopurge temporarily to avoid site slowdowns.', 'kinsta-mu-plugins' ); ?></p>
				<?php if ( get_option( 'kinsta-autopurge-status' ) === 'disabled' ) : ?>
					<a class="button button-primary kinsta-button" href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=kinsta-tools&cache-autopurge=enable' ), 'kinsta-autopurge-toggle', 'kinsta_nonce' ); ?>"><?php esc_html_e( 'Enable Autopurge', 'kinsta-mu-plugins' ); ?></a>
				<?php else : ?>
					<a class="button button-secondary kinsta-button" href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=kinsta-tools&cache-autopurge=disable' ), 'kinsta-autopurge-toggle', 'kinsta_nonce' ); ?>"><?php esc_html_e( 'Disable Autopurge', 'kinsta-mu-plugins' ); ?></a>
				<?php endif; ?>
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
jQuery(document).on('click', '.kinsta-button[data-action]', function() {
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
