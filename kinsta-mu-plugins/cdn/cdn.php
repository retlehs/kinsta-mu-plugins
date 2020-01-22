<?php
/**
 * Initialize CDN Feature
 *
 * @package KinstaMUPlugins
 * @subpackage CDN
 * @since 2.0.0
 */

namespace Kinsta;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

/* Defines */
define( 'KINSTA_CDN_ENABLER_MIN_WP', '3.8' );

/* Requires */
require_once plugin_dir_path( __FILE__ ) . 'utilities.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cdn-enabler.php';
require_once plugin_dir_path( __FILE__ ) . 'class-cdn-rewriter.php';

/* Start CDN related stuff */
$cdn_enabler = new CDN_Enabler();
add_action( 'init', array( $cdn_enabler, 'run' ), 99 );
