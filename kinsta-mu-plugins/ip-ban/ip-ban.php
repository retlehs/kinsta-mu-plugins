<?php
/**
 * IP Ban
 * This module looks out for failed login attempts and notifies our IP logging
 * service
 *
 * @package KinstaMUPlugins
 * @subpackage IPBan
 * @since 1.0.0
 *
 */
namespace Kinsta;

/**
 * IPBan
 * This class contains the code for intercepting failed login attempts and
 * sending them to our logging service
 *
 * @since 1.0.0
 *
 */
class IPBan {

    /**
     * Class constructor
     * Adds the hooks necessary for the logging functionality
     *
     * @since 1.0.0
     * @author Daniel Pataki
     *
     */
    function __construct() {
        add_action( 'wp_login_failed', array( $this, 'sendFailedIP' ), 1 );
        add_action( 'xmlrpc_login_error', array( $this, 'sendFailedIP' ), 1 );
        add_action( 'xmlrpc_pingback_error', array( $this, 'sendFailedIP' ), 1 );
    }

    /**
     * Send Failed IP
     *
     * Sends a post request to our logging service with the IP of the failed
     * login attempt
     *
     * @since 1.0.0
     * @author Daniel Pataki
     *
     */
    function sendFailedIP() {
        if( empty( $_POST ) ) {
            return;
        }

        $data = array(
            'ip' => $_SERVER['REMOTE_ADDR'],
            'secret' => 'kinstaipbanning'
        );

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode( $data )
        );

        $send_ip = wp_remote_post( 'https://my.kinsta.com/log_failed_login', $args );
        return wp_remote_retrieve_response_code( $send_ip );

    }


}

new IpBan;
