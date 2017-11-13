<?php
namespace Kinsta;
new KinstaMUTesting;

class KinstaMUTesting {
    function __construct() {
        add_action( 'init', array( $this, 'custom_post_types' ) );
        add_action( 'init', array( $this, 'custom_taxonomies' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu_item' ) );
        add_action( 'admin_head', array( $this, 'menu_icon_style' ) );

        add_action( 'wp_ajax_kinsta_add_mu_test_content', array( $this, 'action_kinsta_add_mu_test_content' ) );
        add_action( 'wp_ajax_kinsta_remove_mu_test_content', array( $this, 'action_kinsta_remove_mu_test_content' ) );

        add_action( 'wp_ajax_kinsta_test_cache_post', array( $this, 'action_kinsta_test_cache_post' ) );
    }

    function custom_post_types() {
    	$labels = array(
    		'name'               => 'Books',
    		'singular_name'      => 'Book',
    		'menu_name'          => 'Books',
    		'name_admin_bar'     => 'Books',
    		'add_new'            => 'Add New',
    		'add_new_item'       => 'Add New Book',
    		'new_item'           => 'New Book',
    		'edit_item'          => 'Edit Book',
    		'view_item'          => 'View Book',
    		'all_items'          => 'All Books',
    		'search_items'       => 'Search Books',
    		'parent_item_colon'  => 'Parent Books:',
    		'not_found'          => 'No books found.',
    		'not_found_in_trash' => 'No books found in Trash.'
    	);

    	$args = array(
    		'labels'             => $labels,
            'description'        => 'Testing: Custom post type created to test the Kinsta MU plugins',
    		'public'             => true,
    		'publicly_queryable' => true,
    		'show_ui'            => true,
    		'show_in_menu'       => true,
    		'query_var'          => true,
    		'rewrite'            => array( 'slug' => 'book' ),
    		'capability_type'    => 'post',
    		'has_archive'        => true,
    		'hierarchical'       => false,
    		'menu_position'      => null,
            'taxonomies'        => array('post_tag'),
    		'supports'           => array( 'title', 'editor', 'author', 'comments' )
    	);

    	register_post_type( 'book', $args );

    }

    function custom_taxonomies() {

        $labels = array(
            'name'              => 'Genres',
            'singular_name'     => 'Genre',
            'search_items'      => 'Search Genres',
            'all_items'         => 'All Genres',
            'parent_item'       => 'Parent Genre',
            'parent_item_colon' => 'Parent Genre:',
            'edit_item'         => 'Edit Genre',
            'update_item'       => 'Update Genre',
            'add_new_item'      => 'Add New Genre',
            'new_item_name'     => 'New Genre Name',
            'menu_name'         => 'Genre'
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'genre' ),
        );

        register_taxonomy( 'genre', array( 'book', 'post' ), $args );

    }


    function admin_menu_item() {

        add_menu_page(
            'Kinsta MU Test',
            'Kinsta MU Test',
            'manage_options',
            'kinsta-mu-test',
            array( $this, 'admin_menu_page' ),
            'none',
            '122.19992919'
        );
    }

    function menu_icon_style() { ?>
        <style>
            #adminmenu .toplevel_page_kinsta-mu-test .wp-menu-image {
                background-repeat:no-repeat;
                background-position: 50% -28px;
                background-image: url( '<?php echo KinstaTools::shared_resource_url('shared') ?>/images/menu-icon.svg' )
            }
            #adminmenu .toplevel_page_kinsta-mu-test:hover .wp-menu-image,  #adminmenu .toplevel_page_kinsta-mu-test.wp-has-current-submenu .wp-menu-image, #adminmenu .toplevel_page_kinsta-mu-test.current .wp-menu-image {
                background-position: 50% 6px;
            }
        </style>
        <?php
    }

     function admin_menu_page() {
         if( !empty( $_POST ) ) {
             $this->KinstaCache->save_plugin_options();
        }
         include( 'pages/kinstamu-testing.php' );
     }


     function action_kinsta_add_mu_test_content() {
         if ( !isset($_GET['kinstanonce']) || !wp_verify_nonce($_GET['kinstanonce'], 'kinsta_add_mu_test_content')) {
             die();
         }

         $this->add_test_content();
         add_option( 'kinsta_has_mu_test_content', true );
         wp_redirect( admin_url('?page=kinsta-mu-test&kinsta_success=added_content') );
         die();
     }

     function add_test_content() {

         $books = array(
             'Reaper Man','Witches Abroad','Small Gods','Lords and Ladies','Men at Arms','Theatre of Cruelty','Soul Music','Interesting Times','Maskerade','Feet of Clay','Hogfather','Jingo','The Last Continent','Carpe Jugulum','The Fifth Elephant','The Truth','Thief of Time','Night Watch','The Wee Free Men','Monstrous Regiment','A Hat Full of Sky','Going Postal','Thud!','Where\'s my cow','Wintersmith','Making Money','Unseen Academicals','I Shall Wear Midnight','Snuff','Raising Steam',
         );

         $posts = array(
             'A New Publishing Standard','TED Conference Breakdown','Once Upon A Time','Prince Of Persia Guide','Best series of 2017','Glamour','Time And Time Again','For The Love Of Cheese','Game Theory','Board Game Expo','Crates Of Chocolate','The New PHP 7.0','WordPress Breakdown Imminent','PC Game Engines','Political Analysis', 'Cats Vs Dogs','YouTube Today','The Top Ecommerce Stores','TV Buying Guide','News Sources','Say My Name','Top SciFi Films','The Dirty Dozen','SVG Explained','Common Denominators',
             'Level Design','Terry Prarchett Legacy','Comlexity Theory','Reboots','Recipes For Healthy Living'
         );

         $pages = array(
             'About Us','Help','Our Staff','Get Involved','Media'
         );

         $categories = array(
            'thoughts' => 'Thoughts',
            'news' => 'News',
            'politics' => 'Politics',
            'economy' => 'Economy',
            'sports' => 'Sports',
            'weather' => 'Weather',
        );

        $genres = array(
           'scifi' => 'SciFi',
           'horror' => 'Horror',
           'historical' => 'Historical',
           'comedy' => 'Comedy',
           'biography' => 'Biography',
           'fantasy' => 'Fantasy',
       );

       $tags = array(
           'long' => 'Long',
           'short' => 'Short',
           'favorite' => 'Favorite',
           'violent' => 'Violent',
           'easy'    => 'Easy',
           'difficult' => 'Difficult'
       );


       $years = array( '2016', '2015' );
       $months = array( '06', '07', '08', '09' );
       $days = array( '01', '02', '08', '11', '16', '22', '28' );


       foreach( $categories as $slug => $category ) {
           if( !term_exists( $slug, 'category' ) ){
               $term = wp_insert_term( $category,
               'category',
               array(
                   'description' => 'A Kinsta MU Test Term',
                   'slug' => $slug
               ));
               add_term_meta( $term['term_id'], 'kinsta_mu_test_data', true );
           }
       }

       foreach( $genres as $slug => $genre ) {
           if( !term_exists( $slug, 'genre' ) ){
               $term = wp_insert_term( $genre,
               'genre',
               array(
                   'description' => 'A Kinsta MU Test Term',
                   'slug' => $slug
               ));
               add_term_meta( $term['term_id'], 'kinsta_mu_test_data', true );
           }
       }

       foreach( $tags as $slug => $tag ) {
           if( !term_exists( $slug, 'post_tag' ) ){
               $term = wp_insert_term( $tag,
               'post_tag',
               array(
                   'description' => 'A Kinsta MU Test Term',
                   'slug' => $slug
               ));
               add_term_meta( $term['term_id'], 'kinsta_mu_test_data', true );
           }
       }


       foreach( $books as $book ) {
           $date = $years[rand(0,1)] . '-' . $months[rand(0,3)] . '-' . $days[rand(0,6)] . ' ' . rand(11,23) . ':' . rand(11,59) . ':' . rand(11,59);

           $post_data = array(
              'post_title' => $book,
              'post_status' => 'publish',
              'post_date' => $date,
              'post_type' => 'book',
              'post_content' => 'This is a test book created by the Kinsta MU testing plugin.'
          );

          $post_id = wp_insert_post( $post_data );

          $terms_to_add = rand(1,2);
          for( $i=0; $i<$terms_to_add; $i++ ) {
              wp_add_object_terms( $post_id, array_keys($genres)[rand(0,5)], 'genre' );
              wp_add_object_terms( $post_id, array_keys($tags)[rand(0,5)], 'post_tag' );
          }

          add_post_meta( $post_id, 'kinsta_mu_test_data', true );

       }

       foreach( $posts as $post ) {
           $date = $years[rand(0,1)] . '-' . $months[rand(0,3)] . '-' . $days[rand(0,6)] . ' ' . rand(11,23) . ':' . rand(11,59) . ':' . rand(11,59);

           $post_data = array(
              'post_title' => $post,
              'post_status' => 'publish',
              'post_date' => $date,
              'post_type' => 'post',
              'post_content' => 'This is a test book created by the Kinsta MU testing plugin.'
          );

          $post_id = wp_insert_post( $post_data );

          $terms_to_add = rand(1,2);
          for( $i=0; $i<$terms_to_add; $i++ ) {
              wp_add_object_terms( $post_id, array_keys($genres)[rand(0,5)], 'genre' );
              wp_add_object_terms( $post_id, array_keys($categories)[rand(0,5)], 'category' );
              wp_add_object_terms( $post_id, array_keys($tags)[rand(0,5)], 'post_tag' );
          }

          add_post_meta( $post_id, 'kinsta_mu_test_data', true );

       }

       foreach( $pages as $page ) {
           $date = $years[rand(0,1)] . '-' . $months[rand(0,3)] . '-' . $days[rand(0,6)] . ' ' . rand(11,23) . ':' . rand(11,59) . ':' . rand(11,59);

           $post_data = array(
              'post_title' => $page,
              'post_status' => 'publish',
              'post_date' => $date,
              'post_type' => 'page',
              'post_content' => 'This is a test page created by the Kinsta MU testing plugin.'
          );

          $post_id = wp_insert_post( $post_data );

          add_post_meta( $post_id, 'kinsta_mu_test_data', true );

       }


     }

     function delete_test_content() {
         $args = array(
            'post_type' => 'any',
            'meta_query' => array(
                array(
                    'key' => 'kinsta_mu_test_data',
                    'value' => '',
                    'compare' => '!=',
                )
            ),
            'fields' => 'ids',
            'posts_per_page' => -1
         );
         $posts = new WP_Query( $args );

         foreach( $posts->posts as $post ) {
             wp_delete_post( $post, true );
         }

         $terms = get_terms( array(
             'taxonomy' => array('category', 'genre'),
             'hide_empty' => false,
             'meta_query' => array(
                 array(
                     'key' => 'kinsta_mu_test_data',
                     'value' => '',
                     'compare' => '!=',
                 )
             ),
         ) );


         foreach( $terms as $term ) {
             wp_delete_term( $term->term_id, $term->taxonomy );
         }

     }

     function action_kinsta_remove_mu_test_content() {
         if ( !isset($_GET['kinstanonce']) || !wp_verify_nonce($_GET['kinstanonce'], 'kinsta_remove_mu_test_content')) {
             die();
         }

         $this->delete_test_content();
         delete_option( 'kinsta_has_mu_test_content' );
         wp_redirect( admin_url('?page=kinsta-mu-test&kinsta_success=removed_content') );
         die();

     }


     function action_kinsta_test_cache_post() {
         global $KinstaCache;
         if ( !isset($_POST['kinstanonce']) || !wp_verify_nonce($_POST['kinstanonce'], 'test_cache_post' )) {
             die();
         }

         $result = $KinstaCache->KinstaCachePurge->initiate_purge( $_POST['post_id'], $_POST['type'] ) ;

         $response = array(
             'time' => $result['time'],
             'throttled' => array(
                'status' => $result['response']['throttled']['response']['code'],
                'message' => $result['response']['throttled']['response']['message']
            ),
            'immediate' => array(
               'status' => $result['response']['immediate']['response']['code'],
               'message' => $result['response']['immediate']['response']['message']
            ),
            'requests' => $result['requests']
        );

        echo json_encode( $response );
        die();
     }
}
