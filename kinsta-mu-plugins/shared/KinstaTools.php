<?php 
namespace Kinsta;
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * Kinsta Tools
 * KinstaTools contains all the common functionality required for our UI
 *
 * @package KinstaMUPlugins
 * @subpackage KinstaTools
 * @since 1.0.0
 */

class KinstaTools {

    /**
     * Plugin constructor
     * Sets the hooks required for the plugin's functionality
     *
     */
    function __construct() {
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
        add_action( 'wp_ajax_kinsta_save_option', array( $this, 'save_option' ) );
        add_action( 'admin_head', array( $this, 'init_tooltipster') );
        add_action( 'admin_body_class', array( $this, 'body_classes' ) );
        //add_action( 'admin_menu', array( $this, 'admin_menu_item' ) );
        //add_action( 'admin_head', array( $this, 'menu_icon_style' ) );
        add_action( 'parse_request', array( $this, 'kinsta_API_parse_request' ) );

        add_filter( 'query_vars', array( $this, 'kinsta_API_query_vars' ) );
    }

    /**
     * Add Kinsta plugin's API args to the query vars
     *
     * @return object the query_vars object with the extended 
     * @version 1.1
     * @since 2.0.1 resource issue fixing
     * @author laci <laszlo@kinsta.com>
     **/
    function kinsta_API_query_vars( $query_vars ) {
        if ( isset($_GET['zrm3ajc4yvmdbwc78k7c8der3uu47ah6fmx9648g_kinsta_site_api']) ) { $query_vars[] = 'zrm3ajc4yvmdbwc78k7c8der3uu47ah6fmx9648g_kinsta_site_api'; }
        if ( isset($_GET['d2ksbky5nbhjd7zzbu8gtymzc4mz43tcypykubxt_kinsta_site_api']) ) { $query_vars[] = 'd2ksbky5nbhjd7zzbu8gtymzc4mz43tcypykubxt_kinsta_site_api'; }
        return $query_vars;
    }

    /**
     * Check and change response in case of Kinsta plugin's API arg is in the query vars
     *
     * @return void
     * @version 1.1
     * @since 2.0.1 resource issue fixing
     * @author laci <laszlo@kinsta.com>
     **/
    function kinsta_API_parse_request( &$wp ) {
        if ( array_key_exists( 'zrm3ajc4yvmdbwc78k7c8der3uu47ah6fmx9648g_kinsta_site_api', $wp->query_vars ) ) {
            if ( $wp->query_vars['zrm3ajc4yvmdbwc78k7c8der3uu47ah6fmx9648g_kinsta_site_api'] == 'cdninfo' ) include 'api/cdnInfo.api.php';
            return;
        }
        if ( array_key_exists( 'd2ksbky5nbhjd7zzbu8gtymzc4mz43tcypykubxt_kinsta_site_api', $wp->query_vars ) ) {
            if ( $wp->query_vars['d2ksbky5nbhjd7zzbu8gtymzc4mz43tcypykubxt_kinsta_site_api'] == 'siteinfo' ) include 'api/siteInfo.api.php';
            return;
        }
        return;
    }

    /**
     * Whitlable all the Kinsta branded pages and options in the backend
     *
     * @return void
     * @version 1.0 
     * @since 1.0 
     * 
     **/
    function body_classes( $classes ) {
        if( defined('KINSTAMU_WHITELABEL') && KINSTAMU_WHITELABEL === true ) {
            $classes .= " kinstamu-whitelabel";
        }

        return $classes;
    }

    function setOptionsArrayValue( &$array, $name, $value ) {
        if( substr( $name, -1 ) !== ']' ) {
            $array[$name] = $value;
        }
        else {
            $name = str_replace( '][', '|', $name );
            $name = substr( str_replace( '[', '|', $name ), 0, -1 );
            $name = explode( '|', $name );
        }

        $count = count( $name );

        if( $count == 2 ) {
            $array[$name[0]][$name[1]] = $value;
        }

        if( $count == 3 ) {
            $array[$name[0]][$name[1]][$name[2]] = $value;
        }

        if( $count == 4 ) {
            $array[$name[0]][$name[1]][$name[2]][$name[3]] = $value;
        }

    }


    function save_option() {
        if ( ! wp_verify_nonce( $_POST['nonce'], $_POST['name'] ) ) {
            die();
            return;
        }

        $options = get_option( $_POST['option_name'] );
        $this->setOptionsArrayValue( $options, $_POST['name'], $_POST['value'] );
        update_option( $_POST['option_name'], $options );
        die();
    }

    function assets( $page ) {
        if( substr_count( $page , 'kinsta' ) == 0 ) {
            return;
        }

        wp_enqueue_style( 'kinsta-shared', $this->shared_resource_url( 'shared' ) . '/styles/common.css', array(), KINSTAMU_VERSION );
        wp_enqueue_script( 'kinsta-loader', $this->shared_resource_url( 'shared' ) . '/scripts/kinsta-loader.js', array( 'jquery', 'jquery-effects-core' ), KINSTAMU_VERSION, true );
        wp_enqueue_script( 'kinsta-quicksave', $this->shared_resource_url( 'shared' ) . '/scripts/kinsta-quicksave.js', array( 'jquery' ), KINSTAMU_VERSION, true );

        wp_enqueue_script( 'tooltipster', $this->shared_resource_url( 'shared' ) . '/scripts/tooltipster.bundle.min.js', array( 'jquery' ), KINSTAMU_VERSION );
        wp_enqueue_style( 'tooltipster', $this->shared_resource_url( 'shared' ) . '/styles/tooltipster.bundle.css', array(), KINSTAMU_VERSION );

    }

    function init_tooltipster() {
        $screen = get_current_screen();
        if( substr_count( $screen->id , 'kinsta' ) == 0 ) {
            return;
        }

        ?>
        <script>
            jQuery(document).ready(function() {
                jQuery('.kinsta-tooltip').tooltipster({
                    theme: 'tooltipster-borderless',
                    interactive: true,
                    maxWidth: 360
                });
            });
        </script>
        <?php
    }


    /**
     * Load translations
     *
     * @since 1.0.0
     * @author Daniel Pataki
     *
     */
    function load_textdomain() {
        load_muplugin_textdomain('kinsta-tools', dirname( plugin_basename(__FILE__) ) . '/translations' );
    }

    /**
     * Add main Kinsta Tools menu item
     */
    function admin_menu_item() {
        add_menu_page(
            __( 'Kinsta Cache', 'kinsta-tools' ),
            __( 'Kinsta Cache', 'kinsta-tools' ),
            'manage_options',
            'kinsta-tools',
            array( $this, 'admin_menu_page' ),
            'none',
            '3.19992919'
        );
    }

     function admin_menu_page() {

     }


     function menu_icon_style() { ?>
         <style>
             #adminmenu .toplevel_page_kinsta-tools .wp-menu-image {
                 background-repeat:no-repeat;
                 background-position: 50% -28px;
                 background-image: url( '<?php echo $this->shared_resource_url( 'shared' ) ?>images/menu-icon.svg' )
             }
             #adminmenu .toplevel_page_kinsta-tools:hover .wp-menu-image,  #adminmenu .toplevel_page_kinsta-tools.wp-has-current-submenu .wp-menu-image, #adminmenu .toplevel_page_kinsta-tools.current .wp-menu-image {
                 background-position: 50% 6px;
             }
         </style>
         <?php
     }

     public static function kinsta_switch( $option_name, $name, $value, $label, $quicksave = true, $info = false ) {
         $class = ( $quicksave == true ) ? 'kinsta-quicksave' : '';
         ?>
         <div class='kinsta-switch kinsta-control-container <?php echo $class ?>' data-option-name="<?php echo $option_name ?>">

             <label class='kinsta-control-ui'>
                 <input id='<?php echo $name ?>' class="kinsta-control" type='checkbox' name='<?php echo $name ?>' <?php checked( $value, true ) ?> >
                 <span class='kinsta-switch-label' data-on="Yes" data-off="No"></span>
                 <span class='kinsta-switch-handle'></span>
             </label>

             <span class='kinsta-label'><?php echo $label ?></span>
             <input type='hidden' name='kinsta-nonce' value='<?php echo wp_create_nonce( $name ) ?>'>

             <?php if( !empty( $info ) ) : ?>
                  <?php self::kinsta_tooltip( $info, $name ) ?>
            <?php endif ?>

         </div>
         <?php
     }

     public static function kinsta_select_field( $option_name, $name, $value, $label, $quicksave = true, $info = false, $options ) {
         $class = ( $quicksave == true ) ? 'kinsta-quicksave' : '';
         ?>
         <div class='kinsta-select-field kinsta-control-container <?php echo $class ?>' data-option-name="<?php echo $option_name ?>">
             <label>
                 <select name='<?php echo $name ?>' class='kinsta-control'>
                    <?php
                    foreach( $options as $option_value => $option_name ) : ?>
                        <option <?php echo selected( $value, $option_value ) ?> value="<?php echo $option_value ?>"><?php echo $option_name ?></option>
                    <?php endforeach ?>
                 </select>
                 <span class='kinsta-label'><?php echo $label ?></span>
             </label>
             <input type='hidden' name='kinsta-nonce' value='<?php echo wp_create_nonce( $name ) ?>'>

             <?php self::kinsta_tooltip( $info, $name ) ?>

         </div>
         <?php
     }


     public static function kinsta_number_field( $option_name, $name, $value, $label, $quicksave = true, $info = false ) {
         $class = ( $quicksave == true ) ? 'kinsta-quicksave' : '';
         ?>
         <div class='kinsta-number-field kinsta-control-container <?php echo $class ?>' data-option-name="<?php echo $option_name ?>">
             <label>
                 <input type='text' class='kinsta-control' name='<?php echo $name ?>' value='<?php echo $value ?>'>
                 <span class='kinsta-label'><?php echo $label ?></span>
             </label>
             <input type='hidden' name='kinsta-nonce' value='<?php echo wp_create_nonce( $name ) ?>'>

             <?php self::kinsta_tooltip( $info, $name ) ?>

         </div>
         <?php
     }

     public static function kinsta_tooltip( $content, $name ) {
         if( !empty( $content ) ) :
             $name = str_replace( array( '[', ']' ), '_', $name );
        ?>
             <span class="kinsta-tooltip" data-tooltip-content="#kinsta-tooltip-<?php echo $name ?>"><img src='<?php echo $this->shared_resource_url( 'shared' ) ?>/images/info.svg'></span>

             <div class="kinsta-tooltip-content">
                 <span id="kinsta-tooltip-<?php echo $name ?>">
                     <?php echo $content ?>
                 </span>
             </div>
         <?php endif;
     }

    /**
     *
     * Fix missing recource issues with mu plugin's static files
     *
     * It was handled by " plugin_dir_url( __FILE__ )" before we switched to this
     *
     * @author Laci <laszlo@kinsta.com>
     * @version 1.0
     * @since 2.0.1 resource issue fixing
     * @param string $path optional param which is added to the end of the returned string
     *
     */
    public static function shared_resource_url( $path = '' ) {
        $main_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', WPMU_PLUGIN_DIR);
        $main_path = $main_path . '/kinsta-mu-plugins/';
        return $main_path . $path;
     }

}

new KinstaTools;
