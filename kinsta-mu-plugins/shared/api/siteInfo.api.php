<?php 
if ( $_SERVER["REMOTE_ADDR"] == "127.0.0.1" && isset( $_GET['d2ksbky5nbhjd7zzbu8gtymzc4mz43tcypykubxt_kinsta_site_api'] ) ) {

	/* Set the plugin version */
	$kinsta_mu_plugin_about = array(
		'MU_PLUGIN_VERSION' => KINSTAMU_VERSION,
	);

    /* Add the extra variables you might need */
    $kinsta_mu_plugin_about['HOME'] = get_home_url();
    $kinsta_mu_plugin_about['SITE_URL'] = get_site_url();
    

    /* Check if it is a multisite */
    $kinsta_mu_plugin_about['IS_MULTISITE'] = is_multisite();
    $kinsta_mu_plugin_about['IS_SUBDOMAIN'] = ( is_multisite() && defined( 'SUBDOMAIN_INSTALL' ) && SUBDOMAIN_INSTALL  ) ? true : false;
    $kinsta_mu_plugin_about['IS_DOMAINMAPPED'] = ( is_multisite() && defined( 'SUNRISE' ) && SUNRISE == 'on'  ) ? true : false; // Version one


    /* Create the proper return */
    header('Content-Type: application/json');
    echo json_encode($kinsta_mu_plugin_about);
    exit();
    
} else {
	die( 'No script kiddies please!' );
} 

?>
