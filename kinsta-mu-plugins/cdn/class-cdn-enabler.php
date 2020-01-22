<?php
/**
 * CDN Enabler classes
 *
 * Setup the default settings for the Kinsta CDN and communicate with the server
 *
 * @package KinstaMUPlugins
 * @subpackage CDN
 * @since 2.0.0
 */

namespace Kinsta;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

use function Kinsta\CDN\is_rest_api;
use function Kinsta\CDN\is_doing_ajax;
use function Kinsta\CDN\is_admin_referred;
use function Kinsta\CDN\sanitize_exclude_types;

/**
 * CDN Enabler class
 *
 * @version 1.0
 * @author laci
 * @since 2.0.1
 **/
class CDN_Enabler {

	/**
	 * Whether CDN is enabled or not.
	 *
	 * @since 2.0.17
	 * @var bool
	 */
	protected $is_enabled;

	/**
	 * CDN-related options.
	 *
	 * @since 2.0.17
	 * @var array
	 */
	protected $options;

	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct() {

		$this->is_enabled = self::cdn_is_enabled();
		$this->options = self::get_options();
	}

	/**
	 * Run all of the hooks with WordPress.
	 *
	 * @since 2.0.17
	 */
	public function run() {
		if ( ! $this->is_enabled ) {
			return;
		}

		/**
		 * Get the public post types.
		 *
		 * @var array
		 */
		$post_types = get_post_types(
			array(
				'public' => true,
				'show_ui' => true,
			)
		);

		foreach ( $post_types as $key => $post_type ) {
			if ( 'attachment' === $post_type ) {
				continue;
			}
			add_filter( "rest_prepare_{$post_type}", array( $this, 'handle_rest_api_rewrite_hook' ) );
		}

		/**
		 * Rewrite the image URL rendered in an admin-ajax.php request.
		 *
		 * WordPress does not provide a way to filter AJAX response from the admin-ajax.php
		 * endpoint. The only viable way, for the moment, to rewrite image rendered in
		 * the AJAX response is by directly filtering the image `src` and `srcset` URL.
		 *
		 * This hook will also rewrite image URLs exposed in the REST-API for custom resources
		 * and sub-resources where the data structure is not standardized or reliably
		 * guessed.
		 */
		add_filter( 'wp_get_attachment_image_src', array( $this, 'handle_image_src_rewrite_hook' ) );
		add_filter( 'wp_calculate_image_srcset', array( $this, 'handle_image_srcset_rewrite_hook' ) );

		add_action( 'template_redirect', array( $this, 'handle_rewrite_hook' ) );
		add_action( 'all_admin_notices', array( $this, 'requirements_check' ) );
	}

	/**
	 * Check the plugin's requirements
	 *
	 * @return void
	 * @author laci
	 * @since 2.0.1
	 * @version 1.0
	 */
	public function requirements_check() {
		if ( version_compare( $GLOBALS['wp_version'], KINSTA_CDN_ENABLER_MIN_WP . 'alpha', '<' ) && 1 == $_SERVER['KINSTA_CDN_ENABLED'] ) { // WPCS: loose comparison ok.
			show_message(
				sprintf(
					'<div class="error"><p>%s</p></div>',
					sprintf(
						// translators: %1$s minimum WP version, %2$s MyKinsta Dashboard link.
						__( 'Kinsta CDN enabler is optimized for WordPress %1$s. Please disable CDN via %2$s or upgrade your WordPress installation (recommended).', 'kinsta-mu-plugins' ),
						KINSTA_CDN_ENABLER_MIN_WP,
						'<a href="https://my.kinsta.com" target="_blank" title="My Kinsta Dashboard">' . __( 'MyKinsta Dashboard', 'kinsta-mu-plugins' ) . '</a>'
					)
				)
			);
		}
	}

	/**
	 * Get the CDN options
	 *
	 * @return array retunrs the options array
	 * @author laci
	 * @since 2.0.1
	 * @version 0.4
	 */
	public static function get_options() {

		$custom = [];

		if ( defined( 'KINSTA_CDN_USERDIRS' ) && ! empty( KINSTA_CDN_USERDIRS ) ) {
			$custom['dirs'] = 'wp-content,wp-includes,images,' . KINSTA_CDN_USERDIRS;
		}
		if ( isset( $_SERVER['KINSTA_CDN_DOMAIN'] ) && '' !== $_SERVER['KINSTA_CDN_DOMAIN'] ) {
			$custom['url'] = 'https://' . $_SERVER['KINSTA_CDN_DOMAIN'];
		}
		if ( isset( $_SERVER['KINSTA_CDN_DIRECTORIES'] ) && '' !== $_SERVER['KINSTA_CDN_DIRECTORIES'] ) {
			$custom['dirs'] = $_SERVER['KINSTA_CDN_DIRECTORIES'];
		}
		if ( isset( $_SERVER['KINSTA_CDN_EXCLUDE_TYPES'] ) && '' !== $_SERVER['KINSTA_CDN_EXCLUDE_TYPES'] ) {
			$exclude_types = sanitize_exclude_types( $_SERVER['KINSTA_CDN_EXCLUDE_TYPES'] );
			$exclude_types = array_merge( [ '.php' ], $exclude_types );

			$custom['exclude_types'] = $exclude_types;
		}
		if ( isset( $_SERVER['KINSTA_CDN_HTTPS'] ) && '' !== $_SERVER['KINSTA_CDN_HTTPS'] ) {
			$custom['https'] = $_SERVER['KINSTA_CDN_HTTPS'];
		}

		return wp_parse_args(
			$custom,
			[
				'url' => get_option( 'home' ),
				'dirs' => 'wp-content,wp-includes,images',
				'exclude_types' => [ '.php' ],
				'relative' => 1,
				'https' => 1,
			]
		);
	}

	/**
	 * Initiate the rewrite rules for the CDN URL
	 *
	 * @return void
	 * @author laci
	 * @since 2.0.1
	 * @version 1.0
	 */
	public function handle_rewrite_hook() {
		$home_url = get_option( 'home' );

		/* Check if it doesn't need to run */
		if ( ! $this->options || $home_url == $this->options['url'] ) { // WPCS: loose comparison ok.
			return;
		}

		$rewriter = new CDN_Rewriter(
			$this->get_url_to_replace(),
			$this->options['url'],
			$this->options['dirs'],
			$this->options['exclude_types'],
			$this->options['relative'],
			$this->options['https']
		);

		ob_start(
			array( &$rewriter, 'rewrite' )
		);
	}

	/**
	 * Function to rewrite the attachment URL to CDN URL.
	 *
	 * This will affect the URL output of the `wp_get_attachment_url` function.
	 *
	 * @param array|false $image  Either array with src, width & height, icon src, or false.
	 * @return string
	 */
	public function handle_image_src_rewrite_hook( $image ) {

		/**
		 * Check whether the current request is a WordPress AJAX,
		 * or WordPress REST-API request.
		 */

		$doing_ajax = is_doing_ajax();
		$rest_api = is_rest_api();

		if ( ! $doing_ajax && ! $rest_api ) {
			return $image;
		}

		/**
		 * Only rewrite URL renderred on the front-end and rest-api request.
		 * The attachment URL shown on admin page (the Media page as well as the one added to content
		 * in the post editor) should still be pointing to the site URL instead of to the CDN URL.
		 *
		 * It checks both whether the current page is wp-admin as well as whether the request happens
		 * in the admin area. This is because `is_admin()` will return `true` in an admin-ajax.php
		 * request which is something we would like to avoid as we actually want to rewrite
		 * image URL renderred from the admin-ajax.php request.
		 */

		$admin_referred = is_admin_referred();
		$admin_page = is_admin();

		if ( $admin_page && $admin_referred ) {
			return $image;
		}

		$home_url = get_option( 'home' );

		/**
		 * Check if it doesn't need to run.
		 * If it does not, return the image src immediately.
		 */
		if ( ! $this->options || $home_url == $this->options['url'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			return $image;
		}

		$rewriter = new CDN_Rewriter(
			$this->get_url_to_replace(),
			$this->options['url'],
			$this->options['dirs'],
			$this->options['exclude_types'],
			$this->options['relative'],
			$this->options['https']
		);

		if ( is_array( $image ) ) {
			list( $src, $width, $height ) = $image;

			// Value passed to the `rewrite_url` method must be an array.
			return array( $rewriter->rewrite_url( [ $src ] ), $width, $height );
		}

		return $image;
	}

	/**
	 * Function to rewrite the image URL in srcset to CDN URL.
	 *
	 * @param array $sources One or more arrays of source data to include in the 'srcset'.
	 * @return string
	 */
	public function handle_image_srcset_rewrite_hook( $sources ) {

		/**
		 * Check whether the current request is a WordPress AJAX,
		 * or WordPress REST-API request.
		 */

		$doing_ajax = is_doing_ajax();
		$rest_api = is_rest_api();

		if ( ! $doing_ajax && ! $rest_api ) {
			return $sources;
		}

		/**
		 * Only rewrite URL renderred on the front-end and rest-api request.
		 * The attachment URL shown on admin page (the Media page as well as the one added to content
		 * in the post editor) should still be pointing to the site URL instead of to the CDN URL.
		 *
		 * It checks both whether the current page is wp-admin as well as whether the request happens
		 * in the admin area. This is because `is_admin()` will return `true` in an admin-ajax.php
		 * request which is something we would like to avoid as we actually want to rewrite
		 * image URL renderred from the admin-ajax.php request.
		 */

		$admin_referred = is_admin_referred();
		$admin_page = is_admin();

		if ( $admin_page && $admin_referred ) {
			return $sources;
		}

		$home_url = get_option( 'home' );

		/**
		 * Check if it doesn't need to run.
		 * If it does not, return the image srcset immediately.
		 */
		if ( ! $this->options || $home_url == $this->options['url'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			return $sources;
		}

		$rewriter = new CDN_Rewriter(
			$this->get_url_to_replace(),
			$this->options['url'],
			$this->options['dirs'],
			$this->options['exclude_types'],
			$this->options['relative'],
			$this->options['https']
		);

		if ( is_array( $sources ) ) {
			$sources = array_map(
				function( $source ) use ( $rewriter ) {
					$src = $source['url'];
					$source['url'] = $rewriter->rewrite_url( [ $src ] );
					return $source;
				},
				$sources
			);
		}

		return $sources;
	}

	/**
	 * Handle the URL rewrite in WordPress REST-API response.
	 *
	 * This function only handles URL rewrite in the rendered content
	 * in the WordPress REST-API response.
	 *
	 * @since 2.0.17
	 *
	 * @param WP_REST_Response $response The response object.
	 * @return WP_REST_Response Response object.
	 */
	public function handle_rest_api_rewrite_hook( $response ) {
		$home_url = get_option( 'home' );

		/**
		 * Check if it doesn't need to run.
		 * If it does not immediately return the WP_REST_Response.
		 */
		if ( ! $this->options || $home_url == $this->options['url'] ) { // WPCS: loose comparison ok.
			return $response;
		}

		$rewriter = new CDN_Rewriter(
			$this->get_url_to_replace(),
			$this->options['url'],
			$this->options['dirs'],
			$this->options['exclude_types'],
			$this->options['relative'],
			$this->options['https']
		);

		$data = $response->get_data(); // Get the API data.
		if ( isset( $data['content'] ) && isset( $data['content']['rendered'] ) ) {
			$data['content']['rendered'] = $rewriter->rewrite( $data['content']['rendered'] ); // Rewrite the URLs.
		}

		$response->set_data( $data );

		return $response;
	}

	/**
	 * Return if the Kinsta server based CDN service is enabled
	 *
	 * @return boolean
	 * @author laci
	 * @since  2.0.1
	 * @version 1.0.1
	 */
	public static function cdn_is_enabled() {
		return ( isset( $_SERVER['KINSTA_CDN_ENABLED'] ) && 1 == $_SERVER['KINSTA_CDN_ENABLED'] && ( ! defined( 'KINSTA_DEV_ENV' ) || KINSTA_DEV_ENV == false ) ) ? true : false; // WPCS: loose comparison ok.
	}

	/**
	 * Gets the URL which should be replaced
	 *
	 * @since  2.0.20
	 * @return string
	 */
	private function get_url_to_replace() {

		$url = get_option( 'home' );

		if ( defined( 'KINSTA_CDN_USERURL' ) && ! empty( KINSTA_CDN_USERURL ) && is_string( KINSTA_CDN_USERURL ) ) {
			$url = KINSTA_CDN_USERURL;
		}

		return apply_filters( 'kinsta_cdn_url_to_replace', $url );
	}
}

/**
 * Backward compatible.
 * WP Rocket plugin's 3.0.1 version caused fatal error without this.
 */
class CDNEnabler extends CDN_Enabler {} // phpcs:ignore Generic.Files.OneClassPerFile.MultipleFound
