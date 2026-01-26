<?php
/**
 * LiteSpeed Cache Purge class
 *
 * @package KinstaMUPlugins
 * @subpackage Cache
 * @since 2.5.0
 */

namespace Kinsta;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

/**
 * LiteSpeed_Purge class
 *
 * Handles cache purging for LiteSpeed web servers.
 * Supports both native LiteSpeed server purging via headers
 * and the LiteSpeed Cache plugin API when available.
 *
 * @since 2.5.0
 */
class LiteSpeed_Purge {

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
	 * Whether the LiteSpeed Cache plugin is active
	 *
	 * @var boolean
	 */
	public $has_litespeed_plugin;

	/**
	 * URLs queued for purging (batched for header-based purging)
	 *
	 * @var array
	 */
	private $purge_queue = array();

	/**
	 * Constructor
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
		$this->has_litespeed_plugin = $this->detect_litespeed_plugin();

		add_action( 'edit_comment', array( $this, 'comment_edit_actions' ), 10, 2 );
		add_action( 'pre_post_update', array( $this, 'post_unpublished' ), 10, 2 );

		add_action( 'transition_comment_status', array( $this, 'comment_transition_actions' ), 10, 3 );
		add_action( 'transition_post_status', array( $this, 'post_published' ), 10, 3 );

		add_action( 'wp_insert_comment', array( $this, 'comment_insert_actions' ), 10, 2 );
		add_action( 'wp_insert_post', array( $this, 'post_updated' ), 10, 3 );
		add_action( 'wp_trash_post', array( $this, 'post_trashed' ), 10 );
		add_action( 'wp_update_nav_menu', array( $this, 'purge_complete_caches' ) );

		// Send queued purge headers on shutdown.
		add_action( 'shutdown', array( $this, 'send_purge_headers' ), 0 );
	}

	/**
	 * Detect if the LiteSpeed Cache plugin is active
	 *
	 * @return boolean
	 */
	private function detect_litespeed_plugin() {
		// Check if LiteSpeed Cache plugin class exists.
		if ( class_exists( 'LiteSpeed\Core' ) || class_exists( 'LiteSpeed_Cache' ) ) {
			return true;
		}

		// Check if the litespeed_purge action exists.
		if ( has_action( 'litespeed_purge_all' ) ) {
			return true;
		}

		// Check if plugin file exists.
		if ( defined( 'LSCWP_DIR' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if we're running on a LiteSpeed server
	 *
	 * @return boolean
	 */
	public static function is_litespeed_server() {
		// Check server software.
		if ( isset( $_SERVER['SERVER_SOFTWARE'] ) && strpos( $_SERVER['SERVER_SOFTWARE'], 'LiteSpeed' ) !== false ) {
			return true;
		}

		// Check for LSPHP.
		if ( isset( $_SERVER['X_LSPHP'] ) || isset( $_SERVER['HTTP_X_LSPHP'] ) ) {
			return true;
		}

		// Check for LiteSpeed-specific environment variables.
		if ( isset( $_SERVER['LSWS_EDITION'] ) ) {
			return true;
		}

		// Check PHP SAPI.
		if ( strpos( php_sapi_name(), 'litespeed' ) !== false ) {
			return true;
		}

		return false;
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
		if ( 'publish' !== $post_status ) {
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
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post Post object following the update.
	 * @param bool    $update Whether this is an existing post being updated.
	 * @return void
	 */
	public function post_updated( $post_id, $post, $update ) {
		if ( $this->purge_single_happened || wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Clear cache when the post updated, and only when it's already / still published.
		if ( true === $update && 'publish' === get_post_status( $post_id ) ) {
			$this->purge_single_happened = true;
			$this->initiate_purge( $post_id, 'post' );
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
	 * @param  int|string $old_status The old comment status.
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
		return wp_cache_flush();
	}

	/**
	 * Purge the entire LiteSpeed full page cache
	 *
	 * @return bool|WP_Error
	 */
	public function purge_complete_full_page_cache() {
		if ( $this->purge_all_happened ) {
			return true;
		}

		$this->purge_all_happened = true;

		// Method 1: Use LiteSpeed Cache plugin if available.
		if ( $this->has_litespeed_plugin ) {
			do_action( 'litespeed_purge_all' );
			return true;
		}

		// Method 2: Use HTTP header for native LiteSpeed server.
		if ( ! headers_sent() ) {
			header( 'X-LiteSpeed-Purge: *' );
			return true;
		}

		// Method 3: Queue for shutdown if headers already sent.
		$this->purge_queue['purge_all'] = true;
		return true;
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
	 * Purge a specific URL from LiteSpeed cache
	 *
	 * @param string $url The URL to purge.
	 * @return bool
	 */
	public function purge_url( $url ) {
		// Use LiteSpeed Cache plugin if available.
		if ( $this->has_litespeed_plugin ) {
			do_action( 'litespeed_purge_url', $url );
			return true;
		}

		// Queue for header-based purging.
		$path = $this->url_to_purge_path( $url );
		if ( $path ) {
			$this->purge_queue['urls'][] = $path;
		}

		return true;
	}

	/**
	 * Purge URLs by prefix (all URLs starting with the given path)
	 *
	 * @param string $url The URL prefix to purge.
	 * @return bool
	 */
	public function purge_url_prefix( $url ) {
		// Use LiteSpeed Cache plugin if available.
		if ( $this->has_litespeed_plugin ) {
			do_action( 'litespeed_purge_url', $url );
			return true;
		}

		// Queue for header-based purging with prefix syntax.
		$path = $this->url_to_purge_path( $url );
		if ( $path ) {
			// LiteSpeed supports prefix purging.
			$this->purge_queue['prefixes'][] = rtrim( $path, '/' );
		}

		return true;
	}

	/**
	 * Convert URL to LiteSpeed purge path format
	 *
	 * @param string $url The URL to convert.
	 * @return string|false The purge path or false on failure.
	 */
	private function url_to_purge_path( $url ) {
		$parsed = wp_parse_url( $url );
		if ( ! $parsed || empty( $parsed['path'] ) ) {
			return false;
		}

		return $parsed['path'];
	}

	/**
	 * Send queued purge headers on shutdown
	 *
	 * @return void
	 */
	public function send_purge_headers() {
		if ( empty( $this->purge_queue ) || headers_sent() ) {
			return;
		}

		// If purge_all is set, just purge everything.
		if ( ! empty( $this->purge_queue['purge_all'] ) ) {
			header( 'X-LiteSpeed-Purge: *' );
			return;
		}

		$purge_values = array();

		// Add specific URLs.
		if ( ! empty( $this->purge_queue['urls'] ) ) {
			$purge_values = array_merge( $purge_values, array_unique( $this->purge_queue['urls'] ) );
		}

		// Add prefix-based purges.
		if ( ! empty( $this->purge_queue['prefixes'] ) ) {
			foreach ( array_unique( $this->purge_queue['prefixes'] ) as $prefix ) {
				$purge_values[] = $prefix . '*';
			}
		}

		if ( ! empty( $purge_values ) ) {
			// LiteSpeed supports comma-separated purge paths.
			header( 'X-LiteSpeed-Purge: ' . implode( ', ', $purge_values ) );
		}
	}

	/**
	 * Initiate selective purge
	 *
	 * @param int    $post_id The post id.
	 * @param string $event The initiate event.
	 * @return array|false The result or false if disabled.
	 */
	public function initiate_purge( $post_id, $event ) {
		if ( defined( 'KINSTAMU_DISABLE_AUTOPURGE' ) && KINSTAMU_DISABLE_AUTOPURGE === true ) {
			return false;
		}

		$result = array(
			'time' => array( 'start' => microtime( true ) ),
			'method' => $this->has_litespeed_plugin ? 'plugin' : 'headers',
		);

		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}

		// Build the list of URLs to purge.
		$urls_to_purge = $this->build_purge_list( $post );

		// Debug output if enabled.
		if ( defined( 'KINSTA_CACHE_DEBUG' ) && KINSTA_CACHE_DEBUG === true ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions
			echo '<pre>';
			print_r( $urls_to_purge );
			echo '</pre>';
			exit();
		}

		$result['time']['process'] = microtime( true );

		// Purge each URL.
		foreach ( $urls_to_purge['single'] as $key => $url ) {
			$this->purge_url( $url );
		}

		// Purge group URLs (prefix-based).
		foreach ( $urls_to_purge['group'] as $key => $url ) {
			$this->purge_url_prefix( $url );
		}

		/**
		 * Filters for extending purge lists.
		 */
		$urls_to_purge = apply_filters( 'KinstaCache/purgeImmediate', $urls_to_purge ); // phpcs:ignore
		$urls_to_purge = apply_filters( 'LiteSpeedCache/purgeUrls', $urls_to_purge, $post_id ); // phpcs:ignore

		$result['time']['end'] = microtime( true );
		$result['purged_urls'] = $urls_to_purge;

		return $result;
	}

	/**
	 * Build the list of URLs to purge for a post
	 *
	 * @param WP_Post $post The post object.
	 * @return array
	 */
	private function build_purge_list( $post ) {
		$purge_list = array(
			'single' => array(),
			'group' => array(),
		);

		// The post itself.
		$purge_list['single']['post'] = get_permalink( $post->ID );

		// Home page.
		$purge_list['single']['home'] = home_url( '/' );

		// Blog page if different from home.
		if ( ! empty( $this->posts_page_url ) ) {
			$purge_list['single']['blog'] = $this->posts_page_url;
			for ( $i = 2; $i <= $this->immediate_depth; $i++ ) {
				$purge_list['single'][ 'blog_page_' . $i ] = trailingslashit( $this->posts_page_url ) . 'page/' . $i . '/';
			}
		} else {
			for ( $i = 2; $i <= $this->immediate_depth; $i++ ) {
				$purge_list['single'][ 'home_page_' . $i ] = home_url( '/page/' . $i . '/' );
			}
		}

		// Archives.
		$archives = $this->get_post_archives_list( $post );
		$purge_list['single'] = array_merge( $purge_list['single'], $archives['single'] );
		$purge_list['group'] = array_merge( $purge_list['group'], $archives['group'] );

		// Add first pages of archive groups to single purges for immediate effect.
		foreach ( $archives['group'] as $key => $url ) {
			$purge_list['single'][ $key . '_page1' ] = $url;
			for ( $i = 2; $i <= $this->immediate_depth; $i++ ) {
				$purge_list['single'][ $key . '_page' . $i ] = trailingslashit( $url ) . 'page/' . $i . '/';
			}
		}

		// Custom paths.
		$custom_paths = get_option( 'kinsta-cache-additional-paths' );
		if ( ! empty( $custom_paths ) ) {
			foreach ( $custom_paths as $i => $item ) {
				$full_url = home_url( '/' . ltrim( $item['path'], '/' ) );
				if ( 'single' === $item['type'] ) {
					$purge_list['single'][ 'custom_' . $i ] = $full_url;
				} else {
					$purge_list['group'][ 'custom_' . $i ] = $full_url;
				}
			}
		}

		// Feeds.
		$purge_list['single']['feed'] = home_url( '/feed/' );
		$purge_list['single']['feed_rss'] = home_url( '/feed/rss/' );
		$purge_list['single']['feed_rss2'] = home_url( '/feed/rss2/' );
		$purge_list['single']['feed_atom'] = home_url( '/feed/atom/' );

		// Sitemaps.
		$purge_list['group']['sitemap'] = home_url( '/sitemap' );

		// AMP versions.
		$amp_urls = array();
		foreach ( $purge_list['single'] as $key => $url ) {
			if ( strpos( $key, 'feed' ) === false && strpos( $key, 'sitemap' ) === false ) {
				$amp_urls[ $key . '_amp' ] = trailingslashit( $url ) . 'amp/';
			}
		}
		$purge_list['single'] = array_merge( $purge_list['single'], $amp_urls );

		return $purge_list;
	}

	/**
	 * Get the post archive/taxonomy URLs
	 *
	 * @param  object $post WP_Post object.
	 * @return array
	 */
	public function get_post_archives_list( $post ) {
		$purge = array(
			'group' => array(),
			'single' => array(),
		);

		// Author Archive.
		$author_url = get_author_posts_url( $post->post_author );
		if ( $author_url ) {
			$purge['group']['author'] = $author_url;
		}

		// Term Archives.
		$taxonomies = get_taxonomies();
		unset( $taxonomies['nav_menu'] );
		unset( $taxonomies['link_category'] );
		$taxonomies = array_values( $taxonomies );
		$terms = wp_get_object_terms( $post->ID, $taxonomies );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$term_link = get_term_link( $term );
				if ( ! is_wp_error( $term_link ) ) {
					$purge['group'][ 'term_' . $term->taxonomy . '_' . $term->slug ] = $term_link;
				}
			}
		}

		// Date Archives.
		$year = get_the_date( 'Y', $post );
		$month = get_the_date( 'm', $post );
		$day = get_the_date( 'd', $post );

		$purge['single']['year'] = get_year_link( $year );
		$purge['single']['month'] = get_month_link( $year, $month );
		$purge['single']['day'] = get_day_link( $year, $month, $day );

		$purge['group']['year_pages'] = trailingslashit( get_year_link( $year ) ) . 'page/';
		$purge['group']['month_pages'] = trailingslashit( get_month_link( $year, $month ) ) . 'page/';
		$purge['group']['day_pages'] = trailingslashit( get_day_link( $year, $month, $day ) ) . 'page/';

		// Post Type Archive.
		$post_type_archive = get_post_type_archive_link( $post->post_type );
		if ( $post_type_archive && $post_type_archive !== home_url( '/' ) && $post_type_archive !== $this->posts_page_url ) {
			$purge['single']['post_type'] = $post_type_archive;
			$purge['group']['post_type_pages'] = trailingslashit( $post_type_archive ) . 'page/';
		}

		return array_filter( $purge );
	}
}
