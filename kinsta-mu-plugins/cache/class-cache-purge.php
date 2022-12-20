<?php
/**
 * Kinsta Cache Purge classes.
 *
 * @package KinstaMUPlugins
 * @subpackage Cache
 * @since 1.0.0
 */

namespace Kinsta;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

/**
 * Cache_Purge class.
 *
 * Functions to purge the cache upon specific actions such as when a post is updated, a comment is added, etc.
 *
 * @since 1.0.0
 */
class Cache_Purge {

	/**
	 * ID of the Page assigned to display the Blog Posts Index.
	 *
	 * @var int
	 */
	public $posts_page_id;

	/**
	 * URL of the Page assigned to display the Blog Posts Index.
	 *
	 * @var string
	 */
	public $posts_page_url;

	/**
	 * KMP Object.
	 *
	 * @var object
	 */
	public $kmp;

	/**
	 * Number of pages at home or archive page to purge.
	 *
	 * @var int
	 */
	public $immediate_depth;

	/**
	 * Defines if a single purge action happened.
	 *
	 * @var boolean
	 */
	public $purge_single_happened;

	/**
	 * Defines if the all purge action happened.
	 *
	 * @var boolean
	 */
	public $purge_all_happened;

	/**
	 * Constructor.
	 *
	 * Detect actions that causes content to changes root.
	 *
	 * @param object $kmp KMP object.
	 */
	public function __construct( $kmp = false ) {
		if ( false === $kmp ) {
			return;
		}

		// Set our class variables.
		global $wp_rewrite;
		global $wp_version;

		$this->kmp = $kmp;
		$this->kinsta_cache = $kmp->kinsta_cache;
		$this->posts_page_id = get_option( 'page_for_posts' );
		$this->posts_page_url = ( ! empty( $this->posts_page_id ) && ! empty( $wp_rewrite ) && null !== $wp_rewrite ) ? get_permalink( $this->posts_page_id ) : false;
		$this->immediate_depth = 3;
		$this->purge_single_happened = false;
		$this->purge_all_happened = false;

		// Ajax actions for cache clearing.
		add_action( 'wp_ajax_kinsta_clear_all_cache', array( $this, 'action_kinsta_clear_all_cache' ) );
		add_action( 'wp_ajax_kinsta_clear_site_cache', array( $this, 'action_kinsta_clear_site_cache' ) );
		add_action( 'wp_ajax_kinsta_clear_object_cache', array( $this, 'action_kinsta_clear_object_cache' ) );

		// Cache clear for Admin Toolbar.
		add_action( 'admin_init', array( $this, 'clear_cache_admin_bar' ) );
		add_action( 'admin_init', array( $this, 'set_autopurge_option' ) );

		// Comment actions.
		add_action( 'edit_comment', array( $this, 'comment_edit_actions' ), 10, 2 );
		add_action( 'transition_comment_status', array( $this, 'comment_transition_actions' ), 10, 3 );
		add_action( 'wp_insert_comment', array( $this, 'comment_insert_actions' ), 10, 2 );

		// Post type related actions.
		add_action( 'pre_post_update', array( $this, 'post_unpublished' ), 10, 2 );
		add_action( 'transition_post_status', array( $this, 'post_published' ), 10, 3 );
		add_action( 'wp_insert_post', array( $this, 'post_updated' ), 10, 3 );
		add_action( 'wp_trash_post', array( $this, 'post_trashed' ) );

		// Purge all cache on the following hooks.
		$hooks = array(
			'wp_update_nav_menu', // Menu update.
			'edited_term', // Term edit but not add.
			'delete_term', // Term deletion.
		);
		foreach ( $hooks as $hook ) {
			add_action( $hook, array( $this, 'purge_complete_caches' ) );
		}
	}

	/**
	 * When the post status changes to publish, clear the cache.
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post Post object.
	 *
	 * @return void
	 */
	public function post_published( $new_status, $old_status, $post ) {
		if ( $new_status === $old_status || $this->purge_single_happened ) {
			return;
		}

		if ( 'publish' === $new_status ) {
			$this->purge_single_happened = true;
			$this->initiate_purge( $post->ID );
		}
	}

	/**
	 * Fires when a post switches from publish status to another post status such as draft.
	 *
	 * @param int   $post_ID The Post ID.
	 * @param array $updated Array of unslashed post data.
	 *
	 * @return void
	 */
	public function post_unpublished( $post_ID, $updated ) {
		if ( $this->purge_single_happened ) {
			return;
		}

		$post_status = get_post_status( $post_ID );
		if ( 'publish' !== $post_status ) {
			return;
		}

		if ( isset( $updated['post_status'] ) && 'publish' !== $updated['post_status'] ) {
			$this->purge_single_happened = true;
			$this->initiate_purge( $post_ID );
		}
	}

	/**
	 * Figures out which published post is updated and initiates a cache purge with that post.
	 *
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post Post object following the update.
	 * @param bool    $update Whether this is an existing post being updated.
	 *
	 * @return void
	 */
	public function post_updated( $post_id, $post, $update ) {
		if ( $this->purge_single_happened || wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( true === $update && 'publish' === get_post_status( $post_id ) ) {
			$this->purge_single_happened = true;
			$this->initiate_purge( $post_id );
		}
	}

	/**
	 * Clear cache when the post is going to Trash.
	 *
	 * @param int $post_ID Post ID.
	 *
	 * @return void
	 */
	public function post_trashed( $post_ID ) {
		if ( $this->purge_single_happened ) {
			return;
		}

		$this->purge_single_happened = true;
		$this->initiate_purge( $post_ID );
	}

	/**
	 * Figures out if comment status changes and initiates a cache purge with the post.
	 *
	 * @param  int|string $new_status The new comment status.
	 * @param  int}string $old_status The old comment status.
	 * @param WP_Comment $comment The comment data.
	 *
	 * @return void
	 */
	public function comment_transition_actions( $new_status, $old_status, $comment ) {
		if ( 'approved' === $new_status || 'approved' === $old_status ) {
			$this->initiate_purge( $comment->comment_post_ID );
		}
	}

	/**
	 * Figures out if a comment is added and initiates a cache purge with the post.
	 *
	 * @param int        $comment_id The comment's ID.
	 * @param  WP_Comment $comment    The comment data.
	 *
	 * @return void
	 */
	public function comment_insert_actions( $comment_id, $comment ) {
		if ( 1 === (int) $comment->comment_approved ) {
			$this->initiate_purge( $comment->comment_post_ID );
		}
	}

	/**
	 * Figures out if a comment is edited/updated and initiates a cache purge with the post.
	 *
	 * @param  int        $comment_id The comment's ID.
	 * @param array $data The comment data.
	 *
	 * @return void
	 */
	public function comment_edit_actions( $comment_id, $data ) {
		$comment = get_comment( $comment_id );
		if ( 1 === (int) $comment->comment_approved ) {
			$this->initiate_purge( $comment->comment_post_ID );
		}
	}

	/**
	 * Flush the object cache.
	 *
	 * @return bool False on failure, true on success
	 */
	public function purge_complete_object_cache() {
		$response = wp_cache_flush();
		opcache_reset();
		return $response;
	}

	/**
	 * Send the cache purge request.
	 *
	 * @return void
	 **/
	public function purge_complete_site_cache() {
		if ( $this->purge_all_happened ) {
			return;
		}

		$response = wp_remote_get(
			'https://localhost/kinsta-clear-cache-all',
			array(
				'sslverify' => false,
				'timeout' => 5,
			)
		);

		$this->purge_all_happened = true;

		return $response;
	}

	/**
	 * Purge object cache and page cache
	 *
	 * @return void
	 */
	public function purge_complete_caches() {
		$this->purge_complete_object_cache();
		$this->purge_complete_site_cache();

		// Hook that fires after page and object cache is cleared.
		do_action( 'kinsta_purge_complete_caches_happened' );
	}

	/**
	 * Initiate selective purge.
	 *
	 * @param int $post_id the post id.
	 *
	 * @return array the result of the wp_remote_post action
	 **/
	public function initiate_purge( $post_id ) {
		if ( ( defined( 'KINSTAMU_DISABLE_AUTOPURGE' ) && KINSTAMU_DISABLE_AUTOPURGE === true ) || get_option( 'kinsta-autopurge-status') === 'disabled' ) {
			return false;
		}

		$post = get_post( $post_id );
		if ( false === is_post_type_viewable( $post->post_type ) ) {
			return;
		}
		$archives = $this->get_post_archives_list( $post );

		$purge_list = array(
			'throttled' => $archives,
			'immediate' => array(
				'single' => array(),
				'group' => array(),
			),
		);

		// Immediately remove first page of archives.
		foreach ( $archives['group'] as $key => $url ) {
			$purge_list['immediate']['single'][ $key ] = $url;
		}

		$purge_list['immediate']['group']['singular_post'] = get_permalink( $post_id );

		if ( ! empty( $this->posts_page_url ) ) {
			$purge_list['immediate']['single']['home_page'] = home_url() . '/';
			$purge_list['immediate']['single']['blog_page'] = $this->posts_page_url;
		} else {
			$purge_list['immediate']['single']['home_blog_page'] = home_url() . '/';
		}

		if ( ! empty( $purge_list['throttled']['single']['post_type'] ) ) {
			$purge_list['immediate']['single']['post_type'] = $purge_list['throttled']['single']['post_type'];
			unset( $purge_list['throttled']['single']['post_type'] );
		}

		// Add custom URLS.
		$custom_paths = get_option( 'kinsta-cache-additional-paths' );
		if ( ! empty( $custom_paths ) ) {
			foreach ( $custom_paths as $i => $item ) {
				if ( 'single' === $item['type'] ) {
					$purge_list['immediate']['single'][ 'custom|' . $i ] = home_url() . '/' . $item['path'];
				}
				if ( 'group' === $item['type'] ) {
					$purge_list['immediate']['group'][ 'custom|' . $i ] = home_url() . '/' . $item['path'];
				}
			}
		}

		// Add related sitemaps.
		$purge_list['throttled']['group']['sitemap'] = home_url() . '/sitemap';
		// Add feed purging.
		$purge_list['immediate']['group']['feed'] = home_url() . '/feed/';

		// Add AMP immediate single requests.
		foreach ( $purge_list['immediate']['single'] as $key => $value ) {
			if ( substr( $key, 0, 6 ) !== 'custom' ) {
				$purge_list['immediate']['single'][ $key . '|amp' ] = $value . 'amp/';
			}
		}

		// Convert To Request Format.
		$purge_request['throttled'] = $this->convert_purge_list_to_request( $purge_list['throttled'] );
		$purge_request['immediate'] = $this->convert_purge_list_to_request( $purge_list['immediate'] );

		/**
		 * Filters the immediate/throttled cache purge requests.
		 *
		 * @param array $purge_request['type'] The purge request type either immediate or throttled.
		 */
		$purge_request['immediate'] = apply_filters( 'KinstaCache/purgeImmediate', $purge_request['immediate'] ); // phpcs:ignore
		$purge_request['throttled'] = apply_filters( 'KinstaCache/purgeThrottled', $purge_request['throttled'] ); // phpcs:ignore

		$result['requests'] = $purge_request;

		$result['response'] = array(
			'immediate' => $this->send_cache_purge_request( $this->kinsta_cache->config['immediate_path'], $purge_request['immediate'] ),
			'throttled' => $this->send_cache_purge_request( $this->kinsta_cache->config['throttled_path'], $purge_request['throttled'] ),
		);

		// Hook that fires after specific event purges cache.
		do_action( 'kinsta_initiate_purge_happened' );

		if ( defined( 'KINSTA_CACHE_DEBUG' ) && KINSTA_CACHE_DEBUG === true ) {
			$testing = file_put_contents('request-debug.log', json_encode( $purge_request ), FILE_APPEND);
		}
		return $result;
	}

	/**
	 * Send POST request to cache endpoint. Returns array of curl information
	 *
	 * @param string $endpoint_url Endpoint to send purge list to.
	 * @param array  $post_body URLs to send.
	 *
	 * @return array
	 */
	public function send_cache_purge_request( $endpoint_url, $post_body ) {
		$cache_purge_timeout = ( defined( 'KINSTAMU_CACHE_PURGE_TIMEOUT' ) ) ? (int) KINSTAMU_CACHE_PURGE_TIMEOUT : 5;
		$response_data = array(
			'response_code' => 0,
			'error_code' => 0,
			'response_body' => '',
			'error_message' => '',
		);

		$post_request = curl_init( $endpoint_url );
		curl_setopt( $post_request, CURLOPT_POST, true );
		curl_setopt( $post_request, CURLOPT_POSTFIELDS, http_build_query( $post_body ) );
		curl_setopt( $post_request, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $post_request, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $post_request, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt( $post_request, CURLOPT_CONNECTTIMEOUT, $cache_purge_timeout );
		curl_setopt( $post_request, CURLOPT_TIMEOUT, $cache_purge_timeout );
		$response_data['response_body'] = curl_exec( $post_request );
		$response_data['error_code'] = curl_errno( $post_request );
		$response_data['error_message'] = curl_error( $post_request );
		$response_data['response_code'] = curl_getinfo( $post_request, CURLINFO_HTTP_CODE );
		curl_close( $post_request );
		return $response_data;
	}

	/**
	 * Convert to cache purge URL.
	 *
	 * @param  array $purge_list List of URLs.
	 *
	 * @return array
	 */
	public function convert_purge_list_to_request( $purge_list ) {
		$purge = array();

		if ( ! empty( $purge_list['group'] ) ) {
			foreach ( $purge_list['group'] as $key => $value ) {
				$purge[ 'group|' . $key ] = str_replace( array( 'http://', 'https://' ), '', $value );
			}
		}

		if ( ! empty( $purge_list['single'] ) ) {
			foreach ( $purge_list['single'] as $key => $value ) {
				$purge[ 'single|' . $key ] = str_replace( array( 'http://', 'https://' ), '', $value );
			}
		}

		return $purge;
	}

	/**
	 * Get the post archive/taxonomy.
	 *
	 * @param  object $post WP_Post object.
	 *
	 * @return array
	 */
	public function get_post_archives_list( $post ) {
		$taxonomies = get_taxonomies();
		unset( $taxonomies['nav_menu'] );
		unset( $taxonomies['link_category'] );
		$taxonomies = array_values( $taxonomies );
		$terms = wp_get_object_terms( $post->ID, $taxonomies );

		$purge = array(
			'group' => array(),
			'single' => array(),
		);

		// Author archive.
		$purge['group']['author'] = get_author_posts_url( $post->post_author );
		// Term archives.
		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$purge['group'][ 'term|' . $term->taxonomy . '|' . $term->slug ] = get_term_link( $term );
			}
		}

		$year = get_the_date( 'Y', $post );
		$month = get_the_date( 'm', $post );
		$day = get_the_date( 'd', $post );
		$purge['group']['year'] = get_year_link( $year );
		$purge['group']['month'] = get_month_link( $year, $month );
		$purge['group']['day'] = get_day_link( $year, $month, $day );

		$post_type_archive = get_post_type_archive_link( $post->post_type );
		if ( ! ( home_url() === $post_type_archive || $post_type_archive === $this->posts_page_url ) ) {
			$purge['group']['post_type'] = $post_type_archive;
		}

		return array_filter( $purge );
	}

	/**
	 * AJAX Action to clear all cache.
	 *
	 * @return void
	 */
	public function action_kinsta_clear_all_cache() {
		check_ajax_referer( 'kinsta-clear-all-cache', 'kinsta_nonce' );

		$this->purge_complete_caches();

		die();
	}

	/**
	 * AJAX action to clear page cache.
	 *
	 * @return void
	 */
	public function action_kinsta_clear_site_cache() {
		check_ajax_referer( 'kinsta-clear-site-cache', 'kinsta_nonce' );

		$this->purge_complete_site_cache();

		die();
	}

	/**
	 * AJAX action to clear Object Cache.
	 *
	 * @return void
	 */
	public function action_kinsta_clear_object_cache() {
		check_ajax_referer( 'kinsta-clear-object-cache', 'kinsta_nonce' );

		$this->purge_complete_object_cache();

		die();
	}

	/**
	 * * Function to handle Admin Bar cache clear requests.
	 * *
	 * * @return void
	 */
	public function clear_cache_admin_bar() {
		if ( empty( $_GET['page'] ) || empty( $_GET['clear-cache'] ) || ( ! empty( $_GET['page'] ) && 'kinsta-tools' !== $_GET['page'] ) ) {
			return;
		}
		check_admin_referer( 'kinsta-clear-cache-admin-bar', 'kinsta_nonce' );

		if ( 'kinsta-clear-all-cache' === $_GET['clear-cache'] ) {
			$this->purge_complete_caches();
		} elseif ( 'kinsta-clear-object-cache' === $_GET['clear-cache'] ) {
			$this->purge_complete_object_cache();
		} elseif ( 'kinsta-clear-site-cache' === $_GET['clear-cache'] ) {
			$this->purge_complete_site_cache();
		} else {
			return;
		}

		if ( empty( wp_get_referer() ) ) {
			$query_vars = array(
				'page' => 'kinsta-tools',
				'kinsta-cache-cleared' => 'true',
			);
			$redirect_url = add_query_arg( $query_vars, admin_url( 'admin.php' ) );
		} else {
			$redirect_url = add_query_arg( 'kinsta-cache-cleared', 'true', wp_get_referer() );
		}

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * * Function to handle Admin page cache clear requests.
	 * *
	 * * @return void
	 */
	public function clear_cache_admin_page() {
		if ( empty( $_GET['page'] ) || empty( $_GET['clear-cache'] ) || ( ! empty( $_GET['page'] ) && 'kinsta-tools' !== $_GET['page'] ) ) {
			return;
		}
		check_admin_referer( 'kinsta-clear-cache-admin-bar', 'kinsta_nonce' );

		if ( 'kinsta-clear-all-cache' === $_GET['clear-cache'] ) {
			$this->purge_complete_caches();
		} elseif ( 'kinsta-clear-object-cache' === $_GET['clear-cache'] ) {
			$this->purge_complete_object_cache();
		} elseif ( 'kinsta-clear-site-cache' === $_GET['clear-cache'] ) {
			$this->purge_complete_site_cache();
		} else {
			return;
		}

		if ( empty( wp_get_referer() ) ) {
			$query_vars = array(
				'page' => 'kinsta-tools',
				'kinsta-cache-cleared' => 'true',
			);
			$redirect_url = add_query_arg( $query_vars, admin_url( 'admin.php' ) );
		} else {
			$redirect_url = add_query_arg( 'kinsta-cache-cleared', 'true', wp_get_referer() );
		}

		wp_safe_redirect( $redirect_url );
		exit;
	}
	/**
	 * * Function to handle Admin Bar cache clear requests.
	 * *
	 * * @return void
	 */
	public function set_autopurge_option() {
		if ( empty( $_GET['page'] ) || empty( $_GET['cache-autopurge'] ) || ( ! empty( $_GET['page'] ) && 'kinsta-tools' !== $_GET['page'] ) ) {
			return;
		}
		check_admin_referer( 'kinsta-autopurge-toggle', 'kinsta_nonce' );

		if ( 'disable' === $_GET['cache-autopurge'] ) {
			update_option( 'kinsta-autopurge-status', 'disabled' );
		} elseif ( 'enable' === $_GET['cache-autopurge'] ) {
			update_option( 'kinsta-autopurge-status', 'enabled' );
		} else {
			return;
		}

		if ( empty( wp_get_referer() ) ) {
			$query_vars = array(
				'page' => 'kinsta-tools',
				'kinsta-autopurge-updated' => ( $_GET['cache-autopurge'] === 'enable') ? 'enabled' : 'disabled',
			);
			$redirect_url = add_query_arg( $query_vars, admin_url( 'admin.php' ) );
		} else {
			$redirect_url = add_query_arg( 'kinsta-autopurge-updated', ( $_GET['cache-autopurge'] === 'enable') ? 'enabled' : 'disabled', wp_get_referer() );
		}

		wp_safe_redirect( $redirect_url );
		exit;
	}
}
