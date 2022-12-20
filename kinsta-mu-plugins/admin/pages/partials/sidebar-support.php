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
<hr class="kinsta-content-section-split">
<div class='kinsta-content-section'>
	<div class="kinsta-content-section-header">
		<h3><?php esc_html_e( 'Need Help?', 'kinsta-mu-plugins' ); ?></h3>
	</div>
	<div class="kinsta-content-section-body">
		<p><?php esc_html_e( 'If you need some help contact us through your MyKinsta Dashboard', 'kinsta-mu-plugins' ); ?></p>
		<a class="kinsta-button" href="https://my.kinsta.com"><?php esc_html_e( 'Go To Dashboard', 'kinsta-mu-plugins' ); ?></a>
	</div>
</div>
