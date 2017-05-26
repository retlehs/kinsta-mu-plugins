<?php

/**
 * KinstaCache
 * This module allows users to fine tune their cache clearing settings and
 * initiates cache clearing when necessary
 *
 * @package KinstaMUPlugins
 * @subpackage KinstaCache
 * @since 1.0.0
 *
 */
namespace Kinsta;


/**
 * KinstaCache
 * Offers users cache settings and initiates full page and object cache
 * clearing
 *
 * @since 1.0.0
 *
 */
class KinstaCache {

    var $KinstaCacheAdmin;
    var $KinstaCachePurge;
    var $config;
    var $settings;
    var $default_settings;

    function __construct( $config, $default_settings ) {
        $this->config = $config;
        $this->default_settings = $default_settings;
        $this->set_settings();
        $this->KinstaCachePurge = new KinstaCachePurge( $this );
        $this->KinstaCacheAdmin = new KinstaCacheAdmin( $this );
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'wp_ajax_kinsta_clear_cache_all', array( $this, 'action_kinsta_clear_cache_all' ) );
        add_action( 'wp_ajax_kinsta_clear_cache_full_page', array( $this, 'action_kinsta_clear_cache_full_page' ) );
        add_action( 'wp_ajax_kinsta_clear_cache_object', array( $this, 'action_kinsta_clear_cache_object' ) );
        add_action( 'admin_notices', array( $this, 'cleared_cache_notice' ) );

    }

    function cleared_cache_notice() {
        if( !empty( $_GET['kinsta-cache-cleared'] ) && $_GET['kinsta-cache-cleared'] == 'true' ) :
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e( 'Cache cleared successfully', 'kinsta-cache' ); ?></p>
        </div>
        <?php
        endif;
    }

    /**
     * Load translations
     *
     * @since 1.0.0
     * @author Daniel Pataki
     *
     */
    function load_textdomain() {
        load_muplugin_textdomain('kinsta-cache', dirname( plugin_basename(__FILE__) ) . '/translations' );
    }

    function set_settings() {
        $settings = array();
        if( !empty( $this->default_settings ) && !empty( $this->config['option_name'] ) ) {

            // Get settings from database
            $settings = get_option( $this->config['option_name'] );

            // If there are no settings yet, save the default ones
            if( empty( $settings ) ) {
                $settings = $this->default_settings;
                update_option( $this->config['option_name'], $this->default_settings );
            }
        }

        // If there has been a version change scan settings for changes
        if( empty( $settings['version'] ) || empty( $this->default_settings['version'] ) || $settings['version'] != $this->default_settings['version'] ) {
            foreach( $this->default_settings['rules'] as $group => $rules ) {
                // If there is a new rule group add it with the default values
                if( !isset( $settings['rules'][$group] ) ) {
                    $settings['rules'][$group] = $rules;
                }

                // If there are new settings within groups add them with the default value
                foreach( $rules as $name => $value ) {
                    if( !isset( $settings['rules'][$group][$name] ) ) {
                        $settings['rules'][$group][$name] = $this->default_settings['rules'][$group][$name];
                    }
                }
            }

            // Add the new version to the settings
            $settings['version'] = $this->default_settings['version'];

            // Add options
            $settings['options'] = $this->default_settings['options'];

            // Save the modified settings
            update_option( $this->config['option_name'], $settings );
        }

        $this->settings = $settings;
    }

    function save_plugin_options() {
        if ( ! isset( $_POST['kinsta_nonce'] ) || ! wp_verify_nonce( $_POST['kinsta_nonce'], 'save_plugin_options' ) ) {
           exit;
        }

        $new_rules = $_POST['rules'];

        foreach( $this->default_settings['rules'] as $group => $data ) {
            foreach( $data as $rule => $value ) {
                if( empty( $new_rules[$group][$rule] ) ) {
                    $new_rules[$group][$rule] = false;
                }
                else {
                    $new_rules[$group][$rule] = true;
                }
            }
        }

        $this->settings['rules'] = $new_rules;


        $new_options = $_POST['options'];

        foreach( $this->default_settings['options'] as $option => $value) {
            if( !isset( $new_options[$option] ) ) {
                $new_options[$option] = false;
            }
            elseif( $new_options[$option] == 'on' ) {
                $new_options[$option] = true;
            }
        }

        $this->settings['options'] = $new_options;

        update_option( $this->config['option_name'], $this->settings );
    }

    function action_kinsta_clear_cache_all() {
        check_ajax_referer( 'kinsta-clear-cache-all', 'kinsta_nonce' );
        $this->KinstaCachePurge->purge_all();
        if( $_GET['source'] == 'adminbar' ) {
            header( "Location: " . add_query_arg( 'kinsta-cache-cleared', 'true', $_SERVER['HTTP_REFERER'] ) );
        }
        die();
    }

    function action_kinsta_clear_cache_full_page() {
        check_ajax_referer( 'kinsta-clear-cache-full-page', 'kinsta_nonce' );
        $this->KinstaCachePurge->purge_full_page_cache();
        if( $_GET['source'] == 'adminbar' ) {
            header( "Location: " . add_query_arg( 'kinsta-cache-cleared', 'true', $_SERVER['HTTP_REFERER'] ) );
        }
        die();
    }

    function action_kinsta_clear_cache_object() {
        check_ajax_referer( 'kinsta-clear-cache-object', 'kinsta_nonce' );
        $this->KinstaCachePurge->purge_object_cache();
        if( $_GET['source'] == 'adminbar' ) {
            header( "Location: " . add_query_arg( 'kinsta-cache-cleared', 'true', $_SERVER['HTTP_REFERER'] ) );
        }
        die();
    }

}
