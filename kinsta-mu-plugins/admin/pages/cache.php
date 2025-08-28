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
<div id="kinsta-notice" class="notice kinsta-notice notice-success settings-error is-dismissible" hidden>
	<div id="kinsta-notice-content"></div>
	<button type="button" class="notice-dismiss">
		<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'kinsta-mu-plugins' ); ?></span>
	</button>
</div>
<div id="kinsta-wrap">
	<div class='kinsta-page-wrapper'>
		<div class='kinsta-content-section'>
			<div class="kinsta-content-section-header">
				<h3><?php esc_html_e( 'Cache Control', 'kinsta-mu-plugins' ); ?></h3>
			</div>
			<div class="kinsta-content-section-body">
				<p><?php esc_html_e( 'Your site uses our full page and object caching technology to load lightning fast. We purge single pages and key pages such as the home page immediately and impose a minimal throttle time on archive pages. This ensures high availability at all times.', 'kinsta-mu-plugins' ); ?></p>
				<div class="kinsta-button-wrapper">
					<button class="button button-primary kinsta-button" data-action="kinsta_clear_all_cache" data-nonce="<?php echo esc_attr( wp_create_nonce( 'kinsta-clear-all-cache' ) ); ?>" data-done-message="<?php echo esc_html( KMP_Admin::get_done_messages( 'kinsta-clear-all-cache' ) ); ?>" type="submit">
						<?php esc_html_e( 'Clear All Caches', 'kinsta-mu-plugins' ); ?>
					</button>
					<img width="16" height="16" src="<?php echo esc_url( admin_url( 'images/spinner-2x.gif' ) ); ?>" alt="" class="spinner" />
				</div>
			</div>
		</div>
		<hr class="kinsta-content-section-split">
		<div class='kinsta-content-section'>
			<div class="kinsta-content-section-header">
				<h3><?php esc_html_e( 'Site Caching', 'kinsta-mu-plugins' ); ?></h3>
			</div>
			<div class="kinsta-content-section-body">
				<p><?php esc_html_e( 'Site cache makes your site load faster by storing site data. Clear it if you want to make sure your site shows the most recent version.', 'kinsta-mu-plugins' ); ?></p>
				<div class="kinsta-button-wrapper">
					<button class="button button-primary kinsta-button" data-action="kinsta_clear_site_cache" data-nonce="<?php echo esc_attr( wp_create_nonce( 'kinsta-clear-site-cache' ) ); ?>" data-done-message="<?php echo esc_html( KMP_Admin::get_done_messages( 'kinsta-clear-site-cache' ) ); ?>" type="submit">
						<?php esc_html_e( 'Clear Site Cache', 'kinsta-mu-plugins' ); ?>
					</button>
					<img width="16" height="16" src="<?php echo esc_url( admin_url( 'images/spinner-2x.gif' ) ); ?>" alt="" class="spinner" />
				</div>
			</div>
		</div>
		<hr class="kinsta-content-section-split">
		<div class='kinsta-content-section'>
			<div class="kinsta-content-section-header">
				<h3><?php esc_html_e( 'Object Caching', 'kinsta-mu-plugins' ); ?></h3>
			</div>
			<div class="kinsta-content-section-body">
				<p><?php esc_html_e( 'The WordPress Object Cache is used to save on trips to the database. The Object Cache stores all of the cache data to memory and makes the cache contents available by using a key, which is used to name and later retrieve the cache contents.', 'kinsta-mu-plugins' ); ?></p>
				<div class="kinsta-button-wrapper">
					<button class="button button-primary kinsta-button" data-action="kinsta_clear_object_cache" data-nonce="<?php echo esc_attr( wp_create_nonce( 'kinsta-clear-object-cache' ) ); ?>" data-done-message="<?php echo esc_html( KMP_Admin::get_done_messages( 'kinsta-clear-object-cache' ) ); ?>" type="submit">
						<?php esc_html_e( 'Clear Object Cache', 'kinsta-mu-plugins' ); ?>
					</button>
					<img width="16" height="16" src="<?php echo esc_url( admin_url( 'images/spinner-2x.gif' ) ); ?>" alt="" class="spinner" />
				</div>
			</div>
		</div>
		<hr class="kinsta-content-section-split">
		<div class='kinsta-content-section'>
			<div class="kinsta-content-section-header">
				<h3>CDN Caching</h3>
			</div>
			<div class="kinsta-content-section-body">
				<div>
					<p><?php esc_html_e( 'When CDN is enabled, all static content (such as images, CSS, and JavaScript files) is served through our Content Delivery Network. The limit is 5 GB per file. Clearing CDN cache purges the assigned CDN zone. If you replace static files and the new content has the same filename as the old content, you should clear the cache. The process may take up to five minutes.', 'kinsta-mu-plugins' ); ?></p>
				</div>
				<div class="kinsta-button-wrapper">
					<button class="button button-primary kinsta-button" data-action="kinsta_clear_cdn_cache" data-nonce="<?php echo esc_attr( wp_create_nonce( 'kinsta-clear-cdn-cache' ) ); ?>" data-done-message="<?php echo esc_html( KMP_Admin::get_done_messages( 'kinsta-clear-cdn-cache' ) ); ?>" type="submit">
						<?php esc_html_e( 'Clear CDN Cache', 'kinsta-mu-plugins' ); ?>
					</button>
					<img width="16" height="16" src="<?php echo esc_url( admin_url( 'images/spinner-2x.gif' ) ); ?>" alt="" class="spinner" />
				</div>
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
							echo '<td><button class="removePath button-link">Remove</button></td>';
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
							var path = jQuery('#addURLField').val()
								.trim()
								.replace(/^(\.\/+|\.\.\/+)/g, '')
								.replace(/^\/+|\/+$/g, '')
								.replace(/\/{2,}/g, '/');
							var type = jQuery('select[name="custom-url-type"]').val();
							var url = new URL(path, '<?php echo esc_url( home_url( '/' ) ); ?>');
							var urlPath = url.pathname.replace(/^\/+/, '');

							if( urlPath === '' || urlPath === null || typeof urlPath === 'undefined' ) {
								return false
							}

							if (jQuery('#addURLField').val().endsWith('/')) {
								urlPath += '/';
							}

							jQuery.ajax({
								url: ajaxurl,
								method: 'post',
								data: {
									action: 'kinsta_save_custom_path',
									kinsta_nonce: jQuery('#kinsta_nonce').val(),
									path: urlPath,
									type: type
								},
								success: function( result ) {
									jQuery('#addURLField').val('')
									var row = jQuery('<tr></tr>');
									row.append('<td>'+type+'</td>')
									row.append('<td>/'+urlPath+'</td>')
									row.append('<td><button class="removePath button-link">Remove</button></td>')
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
		<form class="kinsta-cache-settings">
			<h3 class="title"><?php esc_html_e( 'Settings', 'kinsta-mu-plugins' ); ?></h3>
			<div class="kinsta-content-section-body no-grid">
				<fieldset>
					<legend class="screen-reader-text"><span>Enable cache autopurge</span></legend>
					<label for="cache-autopurge">
						<input name="kinsta-autopurge-status" type="checkbox" id="cache-autopurge" value="on" <?php checked( in_array( get_option( 'kinsta-autopurge-status', null ), array( 'enabled', null ) ) ); ?> />
						<?php esc_html_e( 'Enable Autopurge', 'kinsta-mu-plugins' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'We purge the full page cache on every page and post update. If you are importing posts, you can disable the autopurge temporarily to avoid site slowdowns.', 'kinsta-mu-plugins' ); ?></p>
				</fieldset>
			</div>
			<div class="kinsta-button-wrapper">
				<button class="button button-primary kinsta-button" id="save-cache-settings" data-action="kinsta_cache_save_settings" data-nonce="<?php echo esc_attr( wp_create_nonce( 'kinsta_nonce' ) ); ?>" data-done-message="<?php esc_html_e( 'Settings saved.', 'kinsta-mu-plugins' ); ?>" type="submit">
					<?php esc_html_e( 'Save Settings', 'kinsta-mu-plugins' ); ?>
				</button>
				<img width="16" height="16" src="<?php echo esc_url( admin_url( 'images/spinner-2x.gif' ) ); ?>" alt="" class="spinner" />
			</div>
		</form>
		<?php
		if ( KINSTAMU_WHITELABEL === false ) {
			include plugin_dir_path( __FILE__ ) . 'partials/sidebar-support.php';
		}
		?>
	</div>
</div>
<style>
#wpbody-content {
	padding-top: 1rem;
}
</style>
<script>
jQuery(document).on('click', '.kinsta-button.button-primary', function(e) {
	e.preventDefault();

	var button = jQuery(this);
	var wrap = jQuery('#kinsta-wrap')
	var action = button.attr('data-action');
	var nonce = button.attr('data-nonce');
	var doneMessage = button.attr('data-done-message');
	var buttonSiblings = wrap.find('.kinsta-button.button-primary').not(button);

	jQuery('.kinsta-notice').each(function() {
		var notice = jQuery(this);

		if (notice.attr('id') === 'kinsta-notice') {
			notice.on('click', '.notice-dismiss', function() {
				notice.hide();
			});
		} else {
			notice.remove();
		}
	});

	if (action && nonce) {
		button.attr('disabled', true);
		button.siblings('.spinner').addClass('is-active');
		buttonSiblings.attr('disabled', true);
		jQuery.ajax({
			url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
			type: 'post',
			data: {
				kinsta_nonce: nonce,
				action: action,
				values: Object.fromEntries(new FormData(button.closest('form')[0]))
			}
		}).done(function(response) {
			/**
			 * Add a success message.
			 *
			 * @todo Add failure message {@link https://kinsta.atlassian.net/browse/KMP-238}
			 */
			var notice = jQuery('#kinsta-notice');
				notice.find('#kinsta-notice-content').html('<p><strong>' + doneMessage + '</strong></p>');
				notice.show();

			button.attr('disabled', false);
			button.siblings('.spinner').removeClass('is-active');
			buttonSiblings.attr('disabled', false);

			jQuery('#wpbody')[0].scrollIntoView({
				behavior: 'smooth',
				block: 'start'
			});
		});
	}

	return false;
});
</script>
