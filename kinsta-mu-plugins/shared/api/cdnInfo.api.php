<?php 
if ( $_SERVER["REMOTE_ADDR"] == "127.0.0.1" && isset( $_GET['zrm3ajc4yvmdbwc78k7c8der3uu47ah6fmx9648g_kinsta_site_api'] ) ) {

	/* Set the plugin version */
	$kinsta_mu_plugin_about = array(
		'MU_PLUGIN_VERSION' => KINSTAMU_VERSION,
	);

	/* Add the extra variables you might need */
    if ( isset( $_SERVER['KINSTA_CDN_ENABLED'] ) ) { $kinsta_mu_plugin_about['KINSTA_CDN_ENABLED'] = $_SERVER['KINSTA_CDN_ENABLED']; }
    if ( isset( $_SERVER['KINSTA_CDN_DOMAIN'] ) ) { $kinsta_mu_plugin_about['KINSTA_CDN_DOMAIN'] = $_SERVER['KINSTA_CDN_DOMAIN']; }
    if ( isset( $_SERVER['KINSTA_CDN_PROVIDER'] ) ) { $kinsta_mu_plugin_about['KINSTA_CDN_PROVIDER'] = $_SERVER['KINSTA_CDN_PROVIDER']; }
    if ( isset( $_SERVER['KINSTA_CDN_HTTPS'] ) ) { $kinsta_mu_plugin_about['KINSTA_CDN_HTTPS'] = $_SERVER['KINSTA_CDN_HTTPS']; }

    /* Create the proper return */
    header('Content-Type: application/json');
    echo json_encode($kinsta_mu_plugin_about);
    exit();
    
} else {
	die( 'No script kiddies please!' );
} 

?>
