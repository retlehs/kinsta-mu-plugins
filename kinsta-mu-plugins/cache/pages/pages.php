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
<div class="wrap">
	<div class='kinsta-page-bar'>
		<?php if ( KINSTAMU_WHITELABEL === false ) : ?>
			<img class='logo' src='<?php echo esc_url( Shared::shared_resource_url( 'shared' ) ); ?>/images/logo-dark.svg' height='16px'>
		<?php elseif ( defined( 'KINSTAMU_LOGO' ) ) : ?>
			<img class='logo' src='<?php echo esc_attr( KINSTAMU_LOGO ); ?>' height='32px'>
		<?php endif ?>
		<h3><?php esc_html_e( 'Cache Control', 'kinsta-mu-plugins' ); ?></h3>
	</div>
	<hr class="wp-header-end" />
	<div class='kinsta-page-wrapper'>
		<div class='kinsta-sidebar'>
			<?php if ( $this->kinsta_cache->has_object_cache ) : ?>
				<?php include plugin_dir_path( __FILE__ ) . 'partials/sidebar-purge-has-object-cache.php'; ?>
			<?php else : ?>
				<?php include plugin_dir_path( __FILE__ ) . 'partials/sidebar-purge-no-object-cache.php'; ?>
			<?php endif ?>
			<?php
			if ( KINSTAMU_WHITELABEL === false ) {
				include plugin_dir_path( __FILE__ ) . 'partials/sidebar-support.php';
			}
			?>
		</div>
		<div class='kinsta-main-content'>
			<div class='kinsta-box'>
				<div class='kinsta-box-title-bar'>
					<?php if ( KINSTAMU_WHITELABEL === false ) : ?>
						<h3><?php esc_html_e( 'Kinsta Cache', 'kinsta-mu-plugins' ); ?></h3>
					<?php else : ?>
						<h3><?php esc_html_e( 'Cache', 'kinsta-mu-plugins' ); ?></h3>
					<?php endif; ?>
				</div>
				<div class='kinsta-box-content'>
					<div class='content mb22'>
						<?php if ( $this->kinsta_cache->has_object_cache ) : ?>
						<p><?php esc_html_e( 'Your site uses our full page and object caching technology to remain lightning fast. We purge single pages and key pages such as the home page immediately and impose a minimal throttle time on archive pages. This ensures high availability at all times.', 'kinsta-mu-plugins' ); ?>
						<?php else : ?>
						<p><?php esc_html_e( 'Your site uses our full page caching technology to remain lightning fast. We purge single pages and key pages such as the home page immediately and impose a minimal throttle time on archive pages. This ensures high availability at all times.', 'kinsta-mu-plugins' ); ?>
						<?php endif; ?>
						</p>
						<?php
						if ( defined( 'KINSTAMU_DISABLE_AUTOPURGE' ) && KINSTAMU_DISABLE_AUTOPURGE === true ) {
							$warning_msg = '<strong>' . __( 'Automatic cache purging has been disabled.', 'kinsta-mu-plugins' ) . '</strong>';
							// Translators: %1$s KINSTAMU_DISABLE_AUTOPURGE, %2$s false.
							$warning_msg .= sprintf( __( 'This means that the page cache stored on the server will not be cleared automatically after a post or page is updated, deleted, or published. If you would like to enable automatic cache purging please remove the %1$s constant from your site\'s wp-config.php file or set its value to %2$s.', 'kinsta-mu-plugins' ), '<code>KINSTAMU_DISABLE_AUTOPURGE</code>', '<code>false</code>' );
							?>
						<div class="kinsta-inbox-message-warning">
							<?php
								echo wp_kses(
									$warning_msg,
									[
										'strong' => true,
										'code' => true,
									]
								);
							?>
						</div>
						<?php } ?>
					</div>
					<?php require plugin_dir_path( __FILE__ ) . 'partials/settings-form.php'; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
jQuery(document).on('click', '.kinsta-clear-cache', function() {
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
