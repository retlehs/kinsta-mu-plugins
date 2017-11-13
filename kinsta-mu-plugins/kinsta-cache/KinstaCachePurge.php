<?php 
namespace Kinsta;
class KinstaCachePurge {

    var $posts_page_id;
    var $posts_page_url;
    var $KinstaCache;
    var $immediate_depth;

    /* EVENT DETECTORS
     * Detect actions that causes content to changessh root 
     */
     function __construct( $KinstaCache ) {
        $this->KinstaCache = $KinstaCache;
        $this->posts_page_id = get_option('page_for_posts');
        // $this->posts_page_url = !empty( $this->posts_page_id ) ? get_permalink( $this->posts_page_id ) : false;
        $this->posts_page_url = ( !empty( $this->posts_page_id ) AND !empty($wp_rewrite) AND $wp_rewrite !== NULL ) ? get_permalink( $this->posts_page_id ) : false;
        $this->immediate_depth = 3;

        add_action( 'transition_post_status', array( $this, 'post_transition_actions' ), 10, 3 );
        add_action( 'post_updated', array( $this, 'post_actions' ), 10, 3 );
        add_action( 'wp_insert_comment', array( $this, 'comment_insert_actions' ), 10, 2 );
        add_action( 'edit_comment', array( $this, 'comment_edit_actions' ), 10, 2 );
        add_action( 'transition_comment_status', array( $this, 'comment_transition_actions' ), 10, 3 );
        add_action( 'wp_update_nav_menu', array( $this, 'purge_complete_caches' ) );


    }

    /* ACTION DETECTORS
     * Figures out which post changes and initiates a cache purge with that post
     */
     function post_transition_actions( $new, $old, $post ) {
         if( $new === 'publish' || $old === 'future' ) {
             $this->initiate_purge( $post->ID, 'post' );
         }
     }

    function post_actions( $post_ID, $new, $old ) {
        if( $new->post_status === 'publish' || $old->post_status === 'publish' ) {
            $this->initiate_purge( $post_ID, 'post' );
        }
    }

    function comment_transition_actions( $new_status, $old_status, $comment ) {
        if( $new_status === 'approved' || $old_status === 'approved' ) {
            $this->initiate_purge( $comment->comment_post_ID, 'comment' );
        }
    }

    function comment_insert_actions( $comment_id, $comment ) {
        if( $comment->comment_approved == 1 ) {
            $this->initiate_purge( $comment->comment_post_ID, 'comment' );
        }
    }

    function comment_edit_actions( $comment_id, $comment ) {
        if( $comment->comment_approved == 1 ) {
            $this->initiate_purge( $comment->comment_post_ID, 'comment' );
        }
    }


    /* CACHE BUSTERS */

    function purge_complete_object_cache() {
        wp_cache_flush();
    }
    /**
     * Send the cache purge request
     *
     * @version 1.1 
     * @return void
     * @author Laci <Laszlo@kinsta.com>
     *
     * @param void
     **/
    function purge_complete_full_page_cache() {
        wp_remote_get(  'https://localhost/kinsta-clear-cache-all', array(
	        'sslverify' => false,
            'timeout' => 5
        ));
    }

    function purge_complete_caches() {
        $this->purge_complete_object_cache();
        $this->purge_complete_full_page_cache();
    }

    /**
     * Initiate selective purge
     *
     * @version 1.1 
     * @author Daniel
     * @author Laci <laszlo@kinsta.com>
     *
     * @param int $post_id the post id
     * @param string $event the initiate event
     *
     * @return array the result of the wp_remote_post action
     **/
    function initiate_purge( $post_id, $event ) {
        $result['time']['start'] = microtime(true);

        $post = get_post( $post_id );


        $archives = $this->get_post_archives_list( $post );

        $purge_list['throttled'] = $archives;
        // Immediately remove first three pages of archives
        foreach( $archives['group'] as $key => $url ) {
            $purge_list['immediate']['single'][$key] = $url;
            for( $i = 2; $i <= $this->immediate_depth; $i++ ) {
                $purge_list['immediate']['single'][$key . '_' . $i] = $url . 'page/' . $i . '/';
            }

        }


        $purge_list['immediate']['group']['singular_post'] = get_permalink( $post_id );

        if( !empty( $this->posts_page_url ) ) {
            $purge_list['immediate']['single']['home_page'] = home_url() . '/';

            $purge_list['immediate']['single']['blog_page'] = $this->posts_page_url;
            for( $i = 2; $i <= $this->immediate_depth; $i++ ) {
                $purge_list['immediate']['single']['blog_page_' . $i] = $this->posts_page_url . '/page/' . $i . '/';
            }

            $purge_list['throttled']['group']['blog_page'] = $purge_list['single']['home_page'] . '/page/';
        }
        else {
            $purge_list['immediate']['single']['home_blog_page'] = home_url() . '/';
            for( $i = 2; $i<=$this->immediate_depth; $i++ ) {
                $purge_list['immediate']['single']['home_blog_page_' . $i] = home_url() . '/page/' . $i . '/';
            }

            $purge_list['throttled']['group']['home_blog_page'] = $purge_list['immediate']['single']['home_blog_page'] . '/page/';
        }

        if( !empty( $purge_list['throttled']['single']['post_type'] ) ) {
            $purge_list['immediate']['single']['post_type'] = $purge_list['throttled']['single']['post_type'];

            unset( $purge_list['throttled']['single']['post_type'] );
        }

        // Add custom URLS

        $custom_paths = get_option( 'kinsta-cache-additional-paths' );
        if( !empty( $custom_paths ) ) {
            foreach( $custom_paths as $i => $item ) {
                if( $item['type'] === 'single' ) {
                    $purge_list['immediate']['single']['custom|' . $i] = home_url() . '/' . $item['path'];
                }
                if( $item['type'] === 'group' ) {
                    $purge_list['immediate']['group']['custom|' . $i] = home_url() . '/' . $item['path'];
                }
            }
        }

        // Convert To Request Format
        $purge_request['throttled'] = $this->convert_purge_list_to_request($purge_list['throttled']);
        $purge_request['immediate'] = $this->convert_purge_list_to_request($purge_list['immediate']);

        $result['requests'] = $purge_request;

        $result['time']['sendrequest'] = microtime(true);

        $result['response']['immediate'] = wp_remote_post( $this->KinstaCache->config['immediate_path'], array(
            'sslverify' => false,
            'timeout' => 5,
            'body' => $purge_request['immediate']
        ));

        $result['response']['throttled'] = wp_remote_post( $this->KinstaCache->config['throttled_path'], array(
            'sslverify' => false,
            'timeout' => 5,
            'body' => $purge_request['throttled']
        ));

        $result['time']['end'] = microtime(true);

        return $result;

    }

    function convert_purge_list_to_request( $purge_list ) {
        $purge = array();
        if( !empty( $purge_list['group'] ) ) {
            foreach( $purge_list['group'] as $key => $value ) {
                $purge['group|' . $key] = str_replace( array('http://', 'https://'), '', $value);
            }
        }
        if( !empty( $purge_list['single'] ) ) {
            foreach( $purge_list['single'] as $key => $value ) {
                $purge['single|' . $key] = str_replace( array('http://', 'https://'), '', $value);
            }
        }
        return $purge;

    }

    /* HELPERS */

    function get_post_archives_list( $post ) {
        // Prepare taxonomies
        $taxonomies = get_taxonomies();
        unset( $taxonomies['nav_menu'] );
        unset( $taxonomies['link_category'] );
        $taxonomies = array_values( $taxonomies );
        $terms = wp_get_object_terms( $post->ID, $taxonomies );

        // Author Archive
        $purge['group']['author'] = get_author_posts_url( $post->post_author );

        // Term Archives
        if( !empty( $terms ) ) {
            foreach( $terms as $term ) {
                $purge['group']['term|' . $term->taxonomy . '|' . $term->slug] = get_term_link( $term );
            }
        }

        $time = strtotime( $post->post_date );
        $year = date( 'Y', $time );
        $month = date( 'm', $time );
        $day = date( 'd', $time );

        $purge['single']['year'] = get_year_link( $year );
        $purge['single']['month'] = get_month_link( $year, $month );
        $purge['single']['day'] = get_day_link( $year, $month, $day );

        $purge['group']['year'] = $purge['single']['year'] . 'page/';
        $purge['group']['month'] = $purge['single']['month'] . 'page/' ;
        $purge['group']['day'] = $purge['single']['day'] . 'page/';

        $post_type_archive = get_post_type_archive_link( $post->post_type );

        if( ! ($post_type_archive === home_url() || $post_type_archive === $this->posts_page_url ) ) {
            $purge['single']['post_type'] = $post_type_archive;
            $purge['group']['post_type'] = $post_type_archive . 'page/';
        }



        return array_filter($purge);
    }

}
