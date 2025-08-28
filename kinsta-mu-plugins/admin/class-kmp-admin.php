<?php
/**
 * Shared classes
 *
 * @package KinstaMUPlugins
 * @subpackage Shared
 * @since 1.0.0
 */

namespace Kinsta;

use Kinsta\KMP\Cache\CustomPaths;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

/**
 * Shared class
 *
 * Common functionalities required in the 'mu-plugins'.
 *
 * @since 1.0.0
 */
class KMP_Admin {
	/**
	 * Kinsta/Cache Object.
	 *
	 * @var object
	 */
	public $kmp;

	/**
	 * Kinsta/Cache Object.
	 *
	 * @var object
	 */
	public $kinsta_cache;

	/**
	 * The role or capability to view and use cache options.
	 *
	 * @var string
	 */
	private $view_role_or_capability;

	/**
	 * Plugin constructor.
	 * Sets the hooks required for the plugin's functionality.
	 *
	 * @param \Kinsta\KMP $kmp The KMP object.
	 */
	public function __construct( \Kinsta\KMP $kmp ) {
		$this->kmp = $kmp;
		$this->kinsta_cache = $kmp->kinsta_cache;
		$this->view_role_or_capability = set_view_role_or_capability();
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );

		if ( KINSTAMU_WHITELABEL === false ) {
			add_filter( 'admin_footer_text', array( $this, 'modify_admin_footer_text' ), 99 );
		}
		// Admin Menu and Admin Toolbar.
		add_action( 'admin_menu', array( $this, 'admin_menu_item' ) );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_item' ), 100 );

		// Custom styling.
		if ( KINSTAMU_WHITELABEL === false ) {
			add_action( 'admin_head', array( $this, 'menu_icon_style' ) );
		}

		// Notice for successful cache clear.
		add_action( 'admin_notices', array( $this, 'cleared_cache_notice' ) );

		// Ajax actions for cache exclusion path management.
		add_action( 'wp_ajax_kinsta_save_custom_path', array( $this, 'action_kinsta_save_custom_path' ) );
		add_action( 'wp_ajax_kinsta_remove_custom_path', array( $this, 'action_kinsta_remove_custom_path' ) );
		add_action( 'wp_ajax_kinsta_cache_save_settings', array( $this, 'action_kinsta_cache_save_settings' ) );

		/**
		 * Filter the page cache supported cache headers
		 * $cache_headers contains List of client caching headers and their (optional) verification callbacks.
		 */
		add_filter(
			'site_status_page_cache_supported_cache_headers',
			function ( $cache_headers ) {
				// Add new header to the existing list.
				$cache_headers['x-kinsta-cache'] = static function ( $header_value ) {
					return false !== strpos( strtolower( $header_value ), 'hit' );
				};
				return $cache_headers;
			}
		);
	}

	/**
	 * Load assets in the Kinsta plugin setting page.
	 *
	 * @param  string $page The page slug.
	 * @return void
	 */
	public function assets( $page ) {
		if ( substr_count( $page, 'kinsta' ) === 0 ) {
			return;
		}

		wp_enqueue_style( 'kinsta-shared', $this->shared_resource_url( 'admin/assets/css/common.css' ), array(), KINSTAMU_VERSION );
	}

	/**
	 * Fix missing recource issues with mu plugin's static files
	 *
	 * It was handled by " plugin_dir_url( __FILE__ )" before we switched to this
	 *
	 * @author Laci <laszlo@kinsta.com>
	 * @version 1.2
	 * @since 2.0.1 resource issue fixing
	 *
	 * @param string $path optional param which is added to the end of the returned string.
	 * @return string URL path of the kinsta-mu-plugins.
	 */
	public static function shared_resource_url( $path = '' ) {
		$mu_url = ( is_ssl() ) ? str_replace( 'http://', 'https://', WPMU_PLUGIN_URL ) : WPMU_PLUGIN_URL;
		$full_path = $mu_url . '/kinsta-mu-plugins/' . $path;

		if ( defined( 'KINSTAMU_CUSTOM_MUPLUGIN_URL' ) && KINSTAMU_CUSTOM_MUPLUGIN_URL !== '' ) {
			$full_path = KINSTAMU_CUSTOM_MUPLUGIN_URL . '/kinsta-mu-plugins/' . $path;
		}

		return $full_path;
	}

	/**
	 * Modify Footer Text
	 * Modifies the thank you text in the bottom of the admin
	 *
	 * @since 1.0.0
	 * @author Daniel Pataki
	 */
	public function modify_admin_footer_text() {
		// Translators: %1$s WordPress, %2$s Kinsta URL.
		return '<span id="footer-thankyou">' . sprintf( __( 'Thanks for creating with %1$s and hosting with %2$s', 'kinsta-mu-plugins' ), '<a href="https://wordpress.org/">WordPress</a>', '<a href="https://kinsta.com/?utm_source=client-wp-admin&utm_medium=bottom-cta" target="_blank">Kinsta</a>' ) . '</span>';
	}

	/**
	 * Add main Kinsta Tools menu item.
	 */
	public function admin_menu_item() {
		/**
		 * Filters whether or not Admin Menu item/page is visible.
		 *
		 * @param bool True to hide the Admin Menu item/page, false to show. Default is false.
		 */
		if ( apply_filters( 'kinsta_admin_disabled', false ) ) {
			return;
		}

		$icon = ( KINSTAMU_WHITELABEL === false ) ? 'dashicons-cloud' : 'dashicons-cloud';
		$title = ( KINSTAMU_WHITELABEL === false ) ? __( 'Kinsta Cache', 'kinsta-mu-plugins' ) : __( 'Server Cache', 'kinsta-mu-plugins' );

		add_menu_page(
			$title,
			$title,
			$this->view_role_or_capability,
			'kinsta-tools',
			array( $this, 'admin_cache_page' ),
			$icon,
			'3.19992919'
		);

		if ( $this->kmp->is_cdn_enabled() ) {
			add_submenu_page(
				'kinsta-tools',
				'CDN',
				'CDN',
				$this->view_role_or_capability,
				'kinsta-cdn',
				array( $this, 'admin_cdn_page' ),
				'3.19992919'
			);

			add_submenu_page(
				'kinsta-tools',
				'Settings',
				'Settings',
				$this->view_role_or_capability,
				'kinsta-settings',
				array( $this, 'admin_settings_page' ),
				'3.19992919'
			);
		}
	}

	/**
	 * Add Admin Toolbar menu.
	 *
	 * @param  object $wp_admin_bar WP_Admin_Bar object.
	 *
	 * @return void
	 */
	public function admin_bar_item( $wp_admin_bar ) {
		/**
		 * Filters whether or not Admin Menu item/page is visible.
		 *
		 * @param bool True to hide the Admin Menu item/page, false to show. Default is false.
		 */
		if ( apply_filters( 'kinsta_admin_disabled', false ) || ! current_user_can( $this->view_role_or_capability ) ) {
			return;
		}

		load_muplugin_textdomain( 'kinsta-mu-plugins', dirname( plugin_basename( __FILE__ ) ) . '/assets/translations' );

		$wp_admin_bar->add_node(
			array(
				'id' => 'kinsta-cache',
				'title' => __( 'Clear Caches', 'kinsta-mu-plugins' ),
				'meta' => array(
					'title' => __( 'Clear Caches', 'kinsta-mu-plugins' ),
					'tabindex' => 0,
				),
				'parent' => 'top-secondary',
			)
		);

		$wp_admin_bar->add_node(
			array(
				'id' => 'kinsta-cache-all',
				'title' => __( 'Clear All Caches', 'kinsta-mu-plugins' ),
				'href' => wp_nonce_url( admin_url( 'admin.php?page=kinsta-tools&clear-cache=kinsta-clear-all-cache' ), 'kinsta-clear-cache-admin-bar', 'kinsta_nonce' ),
				'parent' => 'kinsta-cache',
			)
		);

		$wp_admin_bar->add_node(
			array(
				'id' => 'kinsta-cache-full-page',
				'title' => __( 'Clear Site Cache', 'kinsta-mu-plugins' ),
				'href' => wp_nonce_url( admin_url( 'admin.php?page=kinsta-tools&clear-cache=kinsta-clear-site-cache' ), 'kinsta-clear-cache-admin-bar', 'kinsta_nonce' ),
				'parent' => 'kinsta-cache',
			)
		);

		$wp_admin_bar->add_node(
			array(
				'id' => 'kinsta-cache-cdn',
				'title' => __( 'Clear CDN Cache', 'kinsta-mu-plugins' ),
				'href' => wp_nonce_url( admin_url( 'admin.php?page=kinsta-tools&clear-cache=kinsta-clear-cdn-cache' ), 'kinsta-clear-cache-admin-bar', 'kinsta_nonce' ),
				'parent' => 'kinsta-cache',
			)
		);

		$wp_admin_bar->add_node(
			array(
				'id' => 'kinsta-cache-object',
				'title' => __( 'Clear Object Cache', 'kinsta-mu-plugins' ),
				'href' => wp_nonce_url( admin_url( 'admin.php?page=kinsta-tools&clear-cache=kinsta-clear-object-cache' ), 'kinsta-clear-cache-admin-bar', 'kinsta_nonce' ),
				'parent' => 'kinsta-cache',
			)
		);
	}

	/**
	 * Load the menu page.
	 *
	 * @return void
	 */
	public function admin_settings_page() {
		include plugin_dir_path( __FILE__ ) . 'pages/settings.php';
	}

	/**
	 * Load the menu page.
	 *
	 * @return void
	 */
	public function admin_cache_page() {
		include plugin_dir_path( __FILE__ ) . 'pages/cache.php';
	}

	/**
	 * Load the menu page.
	 *
	 * @return void
	 */
	public function admin_cdn_page() {
		include plugin_dir_path( __FILE__ ) . 'pages/cdn.php';
	}

	/**
	 * Show Kinsta menu icon.
	 *
	 * @return void
	 */
	public function menu_icon_style() {
		?>
	<style>
	/* #adminmenu .toplevel_page_kinsta-tools .wp-menu-image {
		background-repeat:no-repeat;
		background-position: 50% -28px;
		background-image: url( '<?php echo esc_url( KMP_Admin::shared_resource_url( 'admin/assets' ) ); ?>/images/menu-icon.svg' )
	}
	#adminmenu .toplevel_page_kinsta-tools:hover .wp-menu-image,  #adminmenu .toplevel_page_kinsta-tools.wp-has-current-submenu .wp-menu-image, #adminmenu .toplevel_page_kinsta-tools.current .wp-menu-image {
		background-position: 50% 6px;
	} */
	</style>
		<?php
	}

	/**
	 * Notice that shows for successful cache clear.
	 * Query var: kinsta-cache-cleared.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function cleared_cache_notice() {
		$key = sanitize_key( $_GET['kinsta-cache-cleared'] ?? '' );

		if ( ! in_array( $key, array( 'all-cache', 'site-cache', 'object-cache', 'cdn-cache', 'true' ), true ) ) {
			return;
		}

		$key = 'kinsta-clear-' . $key;
		?>
		<div class="notice kinsta-notice notice-success settings-error is-dismissible">
			<p><strong><?php echo esc_html( self::get_done_messages( $key ) ); ?></strong></p>
		</div>
		<?php
	}

	/**
	 * AJAX Action to save custom path
	 *
	 * @return void
	 */
	public function action_kinsta_save_custom_path() {
		check_ajax_referer( 'save_plugin_options', 'kinsta_nonce' );

		( new CustomPaths() )->update(
			array(
				'path' => $_POST['path'] ?? '',
				'type' => $_POST['type'] ?? '',
			)
		);

		die();
	}

	/**
	 * AJAX action to remove custom path.
	 *
	 * @return void
	 */
	public function action_kinsta_remove_custom_path() {
		check_ajax_referer( 'save_plugin_options', 'kinsta_nonce' );

		if ( ! isset( $_POST['index'] ) || ( isset( $_POST['index'] ) && is_int( $_POST['index'] ) ) ) {
			return;
		}

		( new CustomPaths() )->remove(
			absint( sanitize_text_field( $_POST['index'] ) ),
		);

		die();
	}

	/**
	 * Action to save settings from the Kinsta Cache settings page.
	 */
	public function action_kinsta_cache_save_settings() {
		check_ajax_referer( 'kinsta_nonce', 'kinsta_nonce' );

		$values = $_POST['values'];

		/**
		 * Handle the autopurge status.
		 *
		 * The value of `kinsta-autopurge-status` is derived from the checkbox
		 * in the settings page. If the checkbox is checked, the value will
		 * be 'on', otherwise it will be excluded in the submitted data,
		 * so we can assume that the checkbox is unchecked.
		 */
		update_option(
			'kinsta-autopurge-status',
			! isset( $values['kinsta-autopurge-status'] ) ? 'disabled' : 'enabled'
		);

		die();
	}

	/**
	 * Retrieve the message after the cache clearing has been initiated,
	 * or when the settings have been saved.
	 *
	 * @param string $key The key to retrieve the message.
	 *
	 * @return string The role or capability.
	 */
	public static function get_done_messages( string $key ): string {

		$messages = array(
			'kinsta-clear-all-cache' => __( 'All caches were cleared. Changes usually appear globally within a few minutes.', 'kinsta-mu-plugins' ),
			'kinsta-clear-site-cache' => __( 'Site cache was cleared. Changes usually appear globally within a few minutes.', 'kinsta-mu-plugins' ),
			'kinsta-clear-object-cache' => __( 'Object cache was cleared. Changes usually appear globally within a few minutes.', 'kinsta-mu-plugins' ),
			'kinsta-clear-cdn-cache' => __( 'CDN cache was cleared. Changes usually appear globally within a few minutes.', 'kinsta-mu-plugins' ),
			'kinsta-cache-save-settings' => __( 'Settings saved.', 'kinsta-mu-plugins' ),
		);

		return $messages[ $key ] ?? __( 'Cache clearing has been initiated.', 'kinsta-mu-plugins' );
	}
}
