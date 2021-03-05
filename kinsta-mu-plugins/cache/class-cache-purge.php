<?php
/**
 * Kinsta Cache Purge classes
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
 * Cache_Purge class
 *
 * Functions to purge the cache upon specific actions,
 * such as when a post is updated, a comment is added, etc.
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
	 * Kinsta Cache Object
	 *
	 * @var object
	 */
	public $kinsta_cache;

	/**
	 * Number of pages at home or archive page to purge
	 *
	 * @var int
	 */
	public $immediate_depth;

	/**
	 * Defines if a single purge action happened
	 *
	 * @var boolean
	 */
	public $purge_single_happened;

	/**
	 * Defines if the all purge action happened
	 *
	 * @var boolean
	 */
	public $purge_all_happened;

	/**
	 * Constructor
	 *
	 * Detect actions that causes content to changessh root
	 *
	 * @param object $kinsta_cache Kinsta Cache object.
	 */
	public function __construct( $kinsta_cache ) {
		global $wp_rewrite;

		$this->kinsta_cache = $kinsta_cache;
		$this->posts_page_id = get_option( 'page_for_posts' );
		$this->posts_page_url = ( ! empty( $this->posts_page_id ) && ! empty( $wp_rewrite ) && null !== $wp_rewrite ) ? get_permalink( $this->posts_page_id ) : false;
		$this->immediate_depth = 3;
		$this->purge_single_happened = false;
		$this->purge_all_happened = false;

		add_action( 'transition_post_status', array( $this, 'post_published' ), 10, 3 );
		add_action( 'pre_post_update', array( $this, 'post_unpublished' ), 10, 2 );
		add_action( 'post_updated', array( $this, 'post_updated' ), 10, 3 );
		add_action( 'wp_trash_post', array( $this, 'post_trashed' ), 10 );

		add_action( 'wp_insert_comment', array( $this, 'comment_insert_actions' ), 10, 2 );
		add_action( 'edit_comment', array( $this, 'comment_edit_actions' ), 10, 2 );
		add_action( 'transition_comment_status', array( $this, 'comment_transition_actions' ), 10, 3 );
		add_action( 'wp_update_nav_menu', array( $this, 'purge_complete_caches' ) );
	}

	/**
	 * Figures out which post changes and initiates a cache purge with that post.
	 *
	 * @param  string $new_status New post status.
	 * @param  string $old_status Old post status.
	 * @param  object $post       WP_Post object.
	 * @return void
	 */
	public function post_published( $new_status, $old_status, $post ) {
		if ( $new_status === $old_status || $this->purge_single_happened ) {
			return;
		}

		// Clear cache when the post is published.
		if ( 'publish' === $new_status ) {
			$this->purge_single_happened = true;
			$this->initiate_purge( $post->ID, 'post' );
		}
	}

	/**
	 * Fires immediately before an existing post is updated in the database.
	 *
	 * @param int   $post_ID The Post Id.
	 * @param array $updated Array of unslashed post data.
	 * @return void
	 */
	public function post_unpublished( $post_ID, $updated ) {
		if ( $this->purge_single_happened ) {
			return;
		}

		$post_status = get_post_status( $post_ID );
		if ( 'publish' !== $post_status ) { // Current post status must be "publish".
			return;
		}

		// Clear cache when the post is unpublished.
		if ( isset( $updated['post_status'] ) && 'publish' !== $updated['post_status'] ) {
			$this->purge_single_happened = true;
			$this->initiate_purge( $post_ID, 'post' );
		}
	}

	/**
	 * Figures out which published post is updated and initiates a cache purge with that post.
	 *
	 * @param int     $post_ID The post ID.
	 * @param WP_Post $post_after Post object following the update.
	 * @param WP_Post $post_before Post object following the update.
	 * @return void
	 */
	public function post_updated( $post_ID, $post_after, $post_before ) {
		if ( $this->purge_single_happened || wp_is_post_autosave( $post_ID ) || wp_is_post_revision( $post_ID ) ) {
			return;
		}

		// Clear cache when the post updated, and only when it's already / still published.
		if ( 'publish' === $post_after->post_status && 'publish' === $post_before->post_status ) {
			$this->purge_single_happened = true;
			$this->initiate_purge( $post_ID, 'post' );
		}
	}

	/**
	 * Clear cache when the post is going to Trash.
	 *
	 * @param int $post_ID Post ID.
	 * @return void
	 */
	public function post_trashed( $post_ID ) {
		if ( $this->purge_single_happened ) {
			return;
		}

		$this->purge_single_happened = true;
		$this->initiate_purge( $post_ID, 'post' );
	}

	/**
	 * Figures out which comment is changed and initiates a cache purge with the post.
	 *
	 * @param  int|string $new_status The new comment status.
	 * @param  int}string $old_status The old comment status.
	 * @param  object     $comment    The comment data.
	 * @return void
	 */
	public function comment_transition_actions( $new_status, $old_status, $comment ) {
		if ( 'approved' === $new_status || 'approved' === $old_status ) {
			$this->initiate_purge( $comment->comment_post_ID, 'comment' );
		}
	}

	/**
	 * Figures out if a comment is added and initiates a cache purge with the post.
	 *
	 * @param  int    $comment_id The comment's ID.
	 * @param  object $comment    The WP_Comment object.
	 * @return void
	 */
	public function comment_insert_actions( $comment_id, $comment ) {
		if ( 1 === $comment->comment_approved ) {
			$this->initiate_purge( $comment->comment_post_ID, 'comment' );
		}
	}

	/**
	 * Figures out if a comment is edited/updated and initiates a cache purge with the post.
	 *
	 * @param  int    $comment_id The comment's ID.
	 * @param  object $comment    The WP_Comment object.
	 * @return void
	 */
	public function comment_edit_actions( $comment_id, $comment ) {
		if ( 1 === $comment->comment_approved ) {
			$this->initiate_purge( $comment->comment_post_ID, 'comment' );
		}
	}

	/**
	 * Flush the object cache.
	 *
	 * @return bool False on failure, true on success
	 */
	public function purge_complete_object_cache() {
		$response = wp_cache_flush();
		return $response;
	}
	/**
	 * Send the cache purge request
	 *
	 * @version 1.1
	 * @return void
	 * @author Laci <Laszlo@kinsta.com>
	 **/
	public function purge_complete_full_page_cache() {
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
		$this->purge_complete_full_page_cache();
	}

	/**
	 * Initiate selective purge
	 *
	 * @version 1.1
	 * @author Daniel Pataki
	 * @author Laci <laszlo@kinsta.com>
	 *
	 * @param int    $post_id the post id.
	 * @param string $event the initiate event.
	 *
	 * @return array the result of the wp_remote_post action
	 **/
	public function initiate_purge( $post_id, $event ) {
		if ( defined( 'KINSTAMU_DISABLE_AUTOPURGE' ) && KINSTAMU_DISABLE_AUTOPURGE === true ) {
			return false;
		}
		$result['time']['start'] = microtime( true );

		$post = get_post( $post_id );

		$archives = $this->get_post_archives_list( $post );

		$purge_list['throttled'] = $archives;

		// Immediately remove first three pages of archives.
		foreach ( $archives['group'] as $key => $url ) {
			$purge_list['immediate']['single'][ $key ] = $url;
			for ( $i = 2; $i <= $this->immediate_depth; $i++ ) {
				$purge_list['immediate']['single'][ $key . '_' . $i ] = $url . 'page/' . $i . '/';
			}
		}

		$purge_list['immediate']['group']['singular_post'] = get_permalink( $post_id );

		if ( ! empty( $this->posts_page_url ) ) {
			$purge_list['immediate']['single']['home_page'] = home_url() . '/';

			$purge_list['immediate']['single']['blog_page'] = $this->posts_page_url;
			for ( $i = 2; $i <= $this->immediate_depth; $i++ ) {
				$purge_list['immediate']['single'][ 'blog_page_' . $i ] = $this->posts_page_url . '/page/' . $i . '/';
			}

			$purge_list['throttled']['group']['blog_page'] = $purge_list['single']['home_page'] . '/page/';
		} else {
			$purge_list['immediate']['single']['home_blog_page'] = home_url() . '/';
			for ( $i = 2; $i <= $this->immediate_depth; $i++ ) {
				$purge_list['immediate']['single'][ 'home_blog_page_' . $i ] = home_url() . '/page/' . $i . '/';
			}

			$purge_list['throttled']['group']['home_blog_page'] = $purge_list['immediate']['single']['home_blog_page'] . '/page/';
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

		// Add sitemap relateds.
		$purge_list['throttled']['group']['sitemap'] = home_url() . '/sitemap';

		// Add feed perging.
		$purge_list['immediate']['single']['feed'] = home_url() . '/feed/';
		$purge_list['immediate']['single']['feed_rss'] = home_url() . '/feed/rss/';
		$purge_list['immediate']['single']['feed_rss2'] = home_url() . '/feed/rss2/';
		$purge_list['immediate']['single']['feed_rdf'] = home_url() . '/feed/rdf/';
		$purge_list['immediate']['single']['feed_atom'] = home_url() . '/feed/atom/';

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
		 * Filters applied.
		 *
		 * TODO Rewrite the filter name to follow WordPress standard, and remove the rule exclusion in the phpcs.xml.
		 */
		$purge_request['immediate'] = apply_filters( 'KinstaCache/purgeImmediate', $purge_request['immediate'] );
		$purge_request['throttled'] = apply_filters( 'KinstaCache/purgeThrottled', $purge_request['throttled'] );

		$result['requests'] = $purge_request;

		// @codingStandardsIgnoreStart
		if ( defined( 'KINSTA_CACHE_DEBUG' ) && KINSTA_CACHE_DEBUG === true ) {
			echo '<pre>';
			print_r( $purge_request );
			echo '</pre>';
			exit();
		}
		// @codingStandardsIgnoreEnd

		$result['time']['sendrequest'] = microtime( true );

		$result['response']['immediate'] = wp_remote_post(
			$this->kinsta_cache->config['immediate_path'],
			array(
				'sslverify' => false,
				'timeout' => 5,
				'body' => $purge_request['immediate'],
			)
		);

		$result['response']['throttled'] = wp_remote_post(
			$this->kinsta_cache->config['throttled_path'],
			array(
				'sslverify' => false,
				'timeout' => 5,
				'body' => $purge_request['throttled'],
			)
		);

		$result['time']['end'] = microtime( true );

		return $result;

	}

	/**
	 * Convert to cache purge URL.
	 *
	 * @param  array $purge_list List of URLs.
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
	 * Get the post archive/taxonomy
	 *
	 * @param  object $post WP_Post object.
	 * @return array
	 */
	public function get_post_archives_list( $post ) {

		// Prepare taxonomies.
		$taxonomies = get_taxonomies();
		unset( $taxonomies['nav_menu'] );
		unset( $taxonomies['link_category'] );
		$taxonomies = array_values( $taxonomies );
		$terms = wp_get_object_terms( $post->ID, $taxonomies );

		// Author Archive.
		$purge['group']['author'] = get_author_posts_url( $post->post_author );

		// Term Archives.
		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$purge['group'][ 'term|' . $term->taxonomy . '|' . $term->slug ] = get_term_link( $term );
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
		$purge['group']['month'] = $purge['single']['month'] . 'page/';
		$purge['group']['day'] = $purge['single']['day'] . 'page/';

		$post_type_archive = get_post_type_archive_link( $post->post_type );

		if ( ! ( home_url() === $post_type_archive || $post_type_archive === $this->posts_page_url ) ) {
			$purge['single']['post_type'] = $post_type_archive;
			$purge['group']['post_type'] = $post_type_archive . 'page/';
		}

		return array_filter( $purge );
	}

}
