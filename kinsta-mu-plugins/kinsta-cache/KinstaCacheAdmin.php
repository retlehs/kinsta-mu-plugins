<?php

namespace Kinsta;

class KinstaCacheAdmin {

    var $KinstaCache;
    var $KinstaCachePurge;

    function __construct( $KinstaCache ) {
        $this->KinstaCache = $KinstaCache;
        $this->KinstaCachePurge = $this->KinstaCache->KinstaCachePurge;
        add_action( 'admin_menu', array( $this, 'admin_menu_item' ) );
        add_action( 'admin_head', array( $this, 'menu_icon_style' ) );
        add_action( 'admin_bar_menu', array( $this, 'admin_bar_item' ), 100 );
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

    function admin_bar_item( $wp_admin_bar ) {
        if ( !current_user_can( 'manage_options' ) ) {
            return;
        }


        if( $this->KinstaCachePurge->has_object_cache ) {

            $wp_admin_bar->add_node( array(
                'id' => 'kinsta-cache',
                'title' => __( 'Clear Cache', 'kinsta-cache' ),
                'meta' => array( 'title' => __( 'Clear Cache', 'kinsta-cache' ) ),
                'parent' => 'top-secondary'
            ));

            $wp_admin_bar->add_node( array(
                'id'     => 'kinsta-cache-all',
                'title'  => 'Clear All Caches',
                'href' => wp_nonce_url( admin_url( 'admin-ajax.php?action=kinsta_clear_cache_all&source=adminbar' ), 'kinsta-clear-cache-all', 'kinsta_nonce' ),
                'parent' => 'kinsta-cache'
            ));


            $wp_admin_bar->add_node( array(
                'id'     => 'kinsta-cache-full-page',
                'title'  => 'Clear Full Page Cache',
                'href' => wp_nonce_url( admin_url( 'admin-ajax.php?action=kinsta_clear_cache_full_page&source=adminbar' ), 'kinsta-clear-cache-full-page', 'kinsta_nonce' ),
                'parent' => 'kinsta-cache'
            ));

            $wp_admin_bar->add_node( array(
                'id'     => 'kinsta-cache-object',
                'title'  => 'Clear Object Cache',
                'href' => wp_nonce_url( admin_url( 'admin-ajax.php?action=kinsta_clear_cache_object&source=adminbar' ), 'kinsta-clear-cache-object', 'kinsta_nonce' ),
                'parent' => 'kinsta-cache'
            ));

        }
        else {
            $wp_admin_bar->add_node( array(
                'id' => 'kinsta-cache',
                'title' => __( 'Clear Cache', 'kinsta-cache' ),
                'href' => wp_nonce_url( admin_url( 'admin-ajax.php?action=kinsta_clear_cache_full_page&source=adminbar' ), 'kinsta-clear-cache-full-page', 'kinsta_nonce' ),
                'meta' => array( 'title' => __( 'Clear Cache', 'kinsta-cache' ) ),
                'parent' => 'top-secondary'
            ));

        }

    }


     function admin_menu_page() {
         if( !empty( $_POST ) ) {
             $this->KinstaCache->save_plugin_options();
        }
         include( 'pages/kinsta-cache.php' );
     }


     function menu_icon_style() { ?>
         <style>
             #adminmenu .toplevel_page_kinsta-tools .wp-menu-image {
                 background-repeat:no-repeat;
                 background-position: 50% -28px;
                 background-image: url( '<?php echo plugin_dir_url( __FILE__ ) ?>../shared/images/menu-icon.svg' )
             }
             #adminmenu .toplevel_page_kinsta-tools:hover .wp-menu-image,  #adminmenu .toplevel_page_kinsta-tools.wp-has-current-submenu .wp-menu-image, #adminmenu .toplevel_page_kinsta-tools.current .wp-menu-image {
                 background-position: 50% 6px;
             }
         </style>
         <?php
     }


}
