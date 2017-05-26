<?php
/**
 * Kinsta Tools
 * KinstaTools contains all the common functionality required for our UI
 *
 * @package KinstaMUPlugins
 * @subpackage KinstaTools
 * @since 1.0.0
 */

namespace Kinsta;

class KinstaTools {

    /**
     * Plugin constructor
     * Sets the hooks required for the plugin's functionality
     */
    function __construct() {
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
        //add_action( 'admin_menu', array( $this, 'admin_menu_item' ) );
        //add_action( 'admin_head', array( $this, 'menu_icon_style' ) );
    }

    function assets( $page ) {
        if( substr_count( $page , 'kinsta' ) == 0 ) {
            return;
        }

        wp_enqueue_style( 'kinsta-shared', plugin_dir_url( __FILE__ ) . '/styles/common.css', array(), KINSTAMU_VERSION );
        wp_enqueue_script( 'kinsta-loader', plugin_dir_url( __FILE__ ) . '/scripts/kinsta-loader.js', array( 'jquery', 'jquery-effects-core' ), KINSTAMU_VERSION, true );

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
                 background-image: url( '<?php echo plugin_dir_url( __FILE__ ) ?>images/menu-icon.svg' )
             }
             #adminmenu .toplevel_page_kinsta-tools:hover .wp-menu-image,  #adminmenu .toplevel_page_kinsta-tools.wp-has-current-submenu .wp-menu-image, #adminmenu .toplevel_page_kinsta-tools.current .wp-menu-image {
                 background-position: 50% 6px;
             }
         </style>
         <?php
     }



     public static function kinsta_switch( $name, $value, $label ) {
         ?>
         <div class='kinsta-switch-container'>
             <label class='kinsta-switch'>
                 <input id='<?php echo $name ?>' class="kinsta-switch-input" type='checkbox' name='<?php echo $name ?>'
                     <?php checked( $value, true ) ?>
                 >
                 <span class='kinsta-switch-label' data-on="Yes" data-off="No"></span>
                 <span class='kinsta-switch-handle'></span>
             </label>

             <label for='<?php echo $name ?>'><?php echo $label ?></label>

         </div>
         <?php
     }

     public static function kinsta_number_field( $name, $value, $label ) {
         ?>
         <div class='kinsta-number-field-container'>
             <label>
                 <input type='text' name='<?php echo $name ?>' value='<?php echo $value ?>'>
                 <span class='kinsta-number-field-label'><?php echo $label ?></span>
             </label>
         </div>
         <?php
     }

}



new KinstaTools;
