<?php
/**
 * CDN Rewriter classes
 *
 * Rewrite URLs to the CDN URL
 *
 * @package KinstaMUPlugins
 * @subpackage CDN
 * @since 2.0.0
 */

namespace Kinsta;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

use function Kinsta\CDN\is_preview_mode;

/**
 * CDN Rewriter class
 **/
class CDN_Rewriter {

	/**
	 * Whether the site is on HTTPS
	 *
	 * @var bool
	 */
	public $https = false;

	/**
	 * The site URL
	 *
	 * @var null|string
	 */
	public $site_url = null;

	/**
	 * The CDN URL
	 *
	 * @var null|string
	 */
	public $cdn_url = null;

	/**
	 * Directories
	 *
	 * @var null|string
	 */
	public $dirs = null;

	/**
	 * List of assets to excludes
	 *
	 * @var array
	 */
	public $exclude_types = array();

	/**
	 * Is relative URL?
	 *
	 * @var bool
	 */
	public $relative = false;

	/**
	 * Class constructor
	 *
	 * @param string $site_url The site URL.
	 * @param string $cdn_url  The CDN URL.
	 * @param string $dirs Comma separated list of directories.
	 * @param array  $exclude_types List of asset types to to exclude from the CDN.
	 * @param bool   $relative Is relative URL?.
	 * @param bool   $https Is HTTPS?.
	 */
	public function __construct( $site_url, $cdn_url, $dirs, array $exclude_types, $relative, $https ) {
		$this->site_url = $site_url;
		$this->cdn_url = $cdn_url;
		$this->dirs = $dirs;
		$this->exclude_types = $exclude_types;
		$this->relative = $relative;
		$this->https = $https;
	}

	/**
	 * Check if the asset should be excluded from the CDN.
	 *
	 * @param string $asset URL of the asset.
	 *
	 * @return boolean
	 */
	protected function exclude_asset( $asset ) {
		foreach ( $this->exclude_types as $exclude ) {
			if ( ! ! $exclude && stristr( $asset, $exclude ) !== false ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Whether the URL should be rewritten.
	 *
	 * Check if the URL to rewrite is pointing to the site, or if
	 * it's already rewritten to a CDN URL (S3, Google Cloud, etc).
	 *
	 * @param string $src_url The URL to rewrite to the CDN URL.
	 * @return boolean
	 */
	public static function maybe_rewrite_url( $src_url = '' ) {

		$home_url = get_option( 'home' );

		/**
		 * Keep using the native PHP function instead of using `wp_parse_url`.
		 * The `wp_parse_url` is only added in WordPress 4.4.0 and some
		 * client site's may still be using older WordPress version.
		 */
		$home_url_host = parse_url( $home_url, PHP_URL_HOST ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url

		if ( ! is_string( $home_url_host ) || empty( $home_url_host ) ) {
			return false;
		}

		$src_url_host = parse_url( $src_url, PHP_URL_HOST ); // phpcs:ignore
		if ( substr( $src_url, 0, 1 ) === '/' || $src_url_host === $home_url_host ) {
			return true;
		}

		return false;
	}

	/**
	 * Rewrite URL
	 *
	 * @param  array $asset Asset URL.
	 * @return string        The asset with CDN URL
	 */
	public function rewrite_url( $asset ) {
		if ( $this->exclude_asset( $asset[0] ) || ! self::maybe_rewrite_url( $asset[0] ) ) {
			return $asset[0];
		}

		if ( is_admin_bar_showing() && is_preview_mode() ) { // WPCS: loose comparison ok, CSRF ok.
			return $asset[0];
		}

		$site_url = $this->relative_url( $this->site_url );
		$subst_urls = [ 'http:' . $site_url ];

		if ( $this->https ) {
			$subst_urls = [
				'http:' . $site_url,
				'https:' . $site_url,
			];
		}

		if ( strpos( $asset[0], '//' ) === 0 ) {
			return str_replace( $site_url, $this->cdn_url, $asset[0] );
		}

		if ( ! $this->relative || strstr( $asset[0], $site_url ) ) {
			return str_replace( $subst_urls, $this->cdn_url, $asset[0] );
		}

		return $this->cdn_url . $asset[0];
	}

	/**
	 * Set to relative URL
	 *
	 * @param  string $url The Asset URL.
	 * @return string      The Asset Relative URL
	 */
	protected function relative_url( $url ) {
		return substr( $url, strpos( $url, '//' ) );
	}

	/**
	 * [get_dir_scope description]
	 *
	 * @return [type] [description]
	 */
	protected function get_dir_scope() {
		$input = explode( ',', $this->dirs );
		if ( '' == $this->dirs || count( $input ) < 1 ) { // WPCS: loose comparison ok.
			return 'wp\-content|wp\-includes';
		}
		return implode( '|', array_map( 'quotemeta', array_map( 'trim', $input ) ) );
	}

	/**
	 * Rewrite the asset requests in the site response
	 *
	 * @param  string $source_html the HTML content of the response.
	 * @return string              the updated HTML content of the response
	 *
	 * @author laci
	 * @version 1.0.2 Fixed some issues related to the dot in the request
	 */
	public function rewrite( $source_html ) {
		if ( ! $this->https && isset( $_SERVER['HTTPS'] ) && 'on' == $_SERVER['HTTPS'] ) {  // WPCS: loose comparison ok.
			return $source_html;
		}

		$dirs = $this->get_dir_scope();
		$site_url = $this->https
			? '(https?:|)' . $this->relative_url( quotemeta( $this->site_url ) )
			: '(http:|)' . $this->relative_url( quotemeta( $this->site_url ) );

		$regex_rule = '#(?<=[(\"\'])';

		if ( $this->relative ) {
			$regex_rule .= '(?:' . $site_url . ')?';
		} else {
			$regex_rule .= $site_url;
		}

		$regex_rule .= '/(?:';
			$regex_rule .= '(?:' . $dirs . ')[^\"\')]+\.';
				$regex_rule .= '(?:ogg|ogv|svg|svgz|eot|otf|woff|woff2|mp4|mp3|ttf|css|js|jpg|jpeg|gif|png|ico|webp|zip|tgz|gz|rar|bz2|doc|xls|exe|ppt|tar|mid|midi|wav|bmp|rtf|pdf|mov)';
				$regex_rule .= '(?:(?:/?\?| )[^/\"\')]*)?';
			$regex_rule .= ')';
		$regex_rule .= '(?=[\"\')])#';

		$return_html = preg_replace_callback( $regex_rule, array( &$this, 'rewrite_url' ), $source_html );
		return $return_html;
	}
}
