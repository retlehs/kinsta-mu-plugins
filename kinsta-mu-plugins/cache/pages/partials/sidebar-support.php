<?php
/**
 * Template to display support box in the sidebar of the setting page
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

<div class='kinsta-box kinsta-widget'>
	<div class='kinsta-box-title-bar'>
		<h3><?php esc_html_e( 'Need Help?', 'kinsta-mu-plugins' ); ?></h3>
	</div>
	<div class="kinsta-box-content kinsta-flex">
		<img class='mr22' src='<?php echo esc_url( Shared::shared_resource_url( 'shared' ) ); ?>/images/icon-support.svg' height='66px'>
		<div>
		<?php
		// Translators: %s '<a href="https://my.kinsta.com/" target="_blank">Kinsta Dashboard</a>.
		$content = sprintf( __( 'If you need some help contact us through your %s', 'kinsta-mu-plugins' ), '<a href="https://my.kinsta.com/" target="_blank">' . __( 'MyKinsta Dashboard', 'kinsta-mu-plugins' ) . '</a>' );

		echo wp_kses(
			$content,
			array(
				'a' => array(
					'href' => true,
					'target' => true,
				),
			)
		);
		?>
		</div>
	</div>
</div>
