<?php
/**
 * Admin Text Modifications
 * This module is for modifying bits and pieces of the admin like the
 * footer thank you text. For more complex modifications use separate
 * modules.
 *
 * @package KinstaMUPlugins
 * @subpackage AdminTextModifications
 * @since 1.0.0
 *
 */
namespace Kinsta;

/**
 * Admin Text modifications
 * This class contains all the modifications
 *
 * @since 1.0.0
 *
 */
class AdminTextModifications {

    /**
     * Class constructor
     * Adds all the hooks needed to display modified text
     *
     * @since 1.0.0
     * @author Daniel Pataki
     *
     */
    function __construct() {
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_filter( 'admin_footer_text', array( $this, 'modify_admin_footer_text' ), 99 );
    }

    /**
     * Load translations
     *
     * @since 1.0.0
     * @author Daniel Pataki
     *
     */
    function load_textdomain() {
        load_muplugin_textdomain('admin-text-modifications', dirname( plugin_basename(__FILE__) ) . '/translations' );
    }

    /**
     * Modify Footer Text
     * Modifies the thank you text in the bottom of the admin
     *
     * @since 1.0.0
     * @author Daniel Pataki
     *
     */
    function modify_admin_footer_text() {
        return '<span id="footer-thankyou">' . __( 'Thanks for creating with <a href="https://wordpress.org/">WordPress</a> and hosting with <a href="https://kinsta.com">Kinsta</a>', 'admin-text-modifications' ) . '</span>';
    }
}

new AdminTextModifications;
