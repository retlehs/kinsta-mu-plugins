<?php 
namespace Kinsta;
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/* Defines */
define( 'KINSTA_CDN_ENABLER_MIN_WP', '3.8' );
define( 'KINSTA_SERVERNAME_CDN_DOMAIN', 'KINSTA_CDN_DOMAIN');
define( 'KINSTA_SERVERNAME_CDN_OTHERDOMAIN', 'KINSTA_CDN_OTHERDOMAINS');

/* Start CDN related stuff */
add_action( 'plugins_loaded', array( 'Kinsta\CDNEnabler', 'instance' ), 99 );

/* Requires */
require_once( 'CDNEnabler.php' );
require_once( 'CDNRewriter.php' );
