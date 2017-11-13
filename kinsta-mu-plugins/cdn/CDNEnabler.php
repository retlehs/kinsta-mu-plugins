<?php 
namespace Kinsta;
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Setup the default settings for the Kinsta CDN and communicate with the server
 *
 * @version 1.0
 * @author laci
 * @since 2.0.1
 * 
 **/
class CDNEnabler {

    /**
     * The class initiate itself
     * @return void 
     * @author laci
     * @since 2.0.1
     * @version 1.0
     */
    public static function instance() { new self(); }

    public function __construct() {
        $is_enabled = self::cdn_is_enabled();
        if ( !$is_enabled ) return;

        add_action( 'template_redirect', array( __CLASS__, 'handle_rewrite_hook' ) );
        add_action( 'all_admin_notices', array( __CLASS__, 'requirements_check' ) );
    }

    /**
     * Check the plugin's requirements
     * @return void 
     * @author laci
     * @since 2.0.1
     * @version 1.0
     * 
     */
    public static function requirements_check() {
        if ( version_compare($GLOBALS['wp_version'], KINSTA_CDN_ENABLER_MIN_WP.'alpha', '<') and $_SERVER['KINSTA_CDN_ENABLED'] == 1 ) {
            show_message(
                sprintf(
                    '<div class="error"><p>%s</p></div>',
                    sprintf(
                        __("Kinsta CDN enabler is optimized for WordPress %s. Please disable CDN via <a href='https://my.kintsa.com' title='My Kinsta Dashboard'>My Kinsta Dashboard</a> or upgrade your WordPress installation (recommended).", "kinsta-cdn-enabler"),
                        KINSTA_CDN_ENABLER_MIN_WP
                    )
                )
            );
        }
    }

    /**
     * Get the CDN options
     * @return array retunrs the options array
     * @author laci
     * @since 2.0.1 
     * @version 0.3
     * 
     */
    public static function get_options() {
        
        $custom = array();
        if ( isset($_SERVER['KINSTA_CDN_DOMAIN']) and $_SERVER['KINSTA_CDN_DOMAIN'] !== "" ) { $custom['url'] = "//" . $_SERVER['KINSTA_CDN_DOMAIN']; } 
        if ( isset($_SERVER['KINSTA_CDN_DIRECTORIES']) and $_SERVER['KINSTA_CDN_DIRECTORIES'] !== "" ) { $custom['dirs'] = $_SERVER['KINSTA_CDN_DIRECTORIES']; }
        if ( isset($_SERVER['KINSTA_CDN_EXEPTIONS']) and $_SERVER['KINSTA_CDN_EXEPTIONS'] !== "" ) { $custom['excludes'] = $_SERVER['KINSTA_CDN_EXEPTIONS']; }
        if ( isset($_SERVER['KINSTA_CDN_HTTPS']) and $_SERVER['KINSTA_CDN_HTTPS'] !== "" ) { $custom['https'] = $_SERVER['KINSTA_CDN_HTTPS']; } 

        return wp_parse_args(
            $custom,
            array(
                'url' => get_option('home'),
                'dirs' => 'wp-content,wp-includes,images',
                'excludes' => '.php',
                'relative' => 1,
                'https' => 1
            )
        );
    }

    /**
     * Initiate the rewrite rules for the CDN URL
     * @return void
     * @author laci
     * @since 2.0.1
     * @version 1.0 
     */
    public static function handle_rewrite_hook() {
        $options = self::get_options();

        /* Check if it doesn't need to run */
        if ( !$options || get_option('home') == $options['url'] ) return;

        $excludes = array_map('trim', explode(',', $options['excludes']));

        $rewriter = new CDNRewriter(
            get_option('home'),
            $options['url'],
            $options['dirs'],
            $excludes,
            $options['relative'],
            $options['https']
        );
        ob_start(
            array(&$rewriter, 'rewrite')
        );
    }

    /**
     * cdn_is_enabled
     * Return if the Kinsta server based CDN service is enabled
     * 
     * @return boolean 
     * @author laci
     * @since  2.0.1 
     * @version 1.0.1
     */
    public static function cdn_is_enabled() {
        return ( isset($_SERVER['KINSTA_CDN_ENABLED']) && $_SERVER['KINSTA_CDN_ENABLED'] == 1 && ( !defined('KINSTA_DEV_ENV') || KINSTA_DEV_ENV == false ) ) ? true : false;
    }


}
