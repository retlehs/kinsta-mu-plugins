<?php
/*
Plugin Name: Kinsta Mu-Plugins
Plugin URI: https://kinsta.com/kinsta-tools/kinsta-mu-plugins.zip
Description: Handles the purge of the server level caching. 
Version: 2.0.2
Author: Kinsta Team
Author URI: https://kinsta.com/about-us/
Text Domain: KinstaMUPlugins
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define( 'KINSTAMU_VERSION', '2.0.2' );
if( !defined('KINSTAMU_WHITELABEL') ) { define('KINSTAMU_WHITELABEL', false); }

/* Include the required parts */
require_once( 'kinsta-mu-plugins/admin-text-modifications/admin-text-modifications.php' );
//require( 'kinsta-mu-plugins/ip-ban/ip-ban.php' ); //handled on server level
require_once( 'kinsta-mu-plugins/shared/KinstaTools.php' );
require_once( 'kinsta-mu-plugins/kinsta-cache/kinsta-cache.php' );
require_once( 'kinsta-mu-plugins/cdn/__InitCDN.php' );

/* For testing and developing purpose */
if( defined('KINSTAMU_TESTING') && KINSTAMU_TESTING === true ) {
    require( 'kinsta-mu-plugins/kinstamu-testing/kinstamu-testing.php' );
}
