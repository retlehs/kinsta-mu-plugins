<?php
/**
 * Kinsta Cache Admin classes.
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
 * Cache_Admin class.
 *
 * @since 1.0.0
 */
class Cache_Admin {

	/**
	 * Kinsta/Cache Object.
	 *
	 * @var object
	 */
	public $kinsta_cache;

	/**
	 * Kinsta/Cache_Purge Object.
	 *
	 * @var object
	 */
	public $kinsta_cache_purge;

	/**
	 * The role or capability to view and use cache options.
	 *
	 * @var string
	 */
	private $view_role_or_capability;

	/**
	 * Constructor class.
	 *
	 * @param object $kinsta_cache Kinsta Cache Object.
	 */
	public function __construct( $kinsta_cache = false ) {
		if ( false === $kinsta_cache ) {
			return;
		}

		// Set our class variables.
		$this->kinsta_cache = $kinsta_cache;
		$this->kinsta_cache_purge = $this->kinsta_cache->kinsta_cache_purge;
		$this->view_role_or_capability = $this->set_view_role_or_capability();

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

		$icon = ( KINSTAMU_WHITELABEL === false ) ? 'none' : 'dashicons-admin-generic';
		$title = ( KINSTAMU_WHITELABEL === false ) ? __( 'Kinsta Cache', 'kinsta-mu-plugins' ) : __( 'Cache Settings', 'kinsta-mu-plugins' );

		add_menu_page(
			$title,
			$title,
			$this->view_role_or_capability,
			'kinsta-tools',
			array( $this, 'admin_menu_page' ),
			$icon,
			'3.19992919'
		);
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

		if ( $this->kinsta_cache->has_object_cache ) {
			$wp_admin_bar->add_node(
				array(
					'id' => 'kinsta-cache',
					'title' => __( 'Clear Cache', 'kinsta-mu-plugins' ),
					'meta' => array(
						'title' => __( 'Clear Cache', 'kinsta-mu-plugins' ),
						'tabindex' => 0,
					),
					'parent' => 'top-secondary',
				)
			);

			$wp_admin_bar->add_node(
				array(
					'id' => 'kinsta-cache-all',
					'title' => 'Clear All Caches',
					'href' => wp_nonce_url( admin_url( 'admin.php?page=kinsta-tools&clear-cache=kinsta-clear-cache-all' ), 'kinsta-clear-cache-admin-bar', 'kinsta_nonce' ),
					'parent' => 'kinsta-cache',
				)
			);

			$wp_admin_bar->add_node(
				array(
					'id' => 'kinsta-cache-full-page',
					'title' => 'Clear Full Page Cache',
					'href' => wp_nonce_url( admin_url( 'admin.php?page=kinsta-tools&clear-cache=kinsta-clear-cache-full-page' ), 'kinsta-clear-cache-admin-bar', 'kinsta_nonce' ),
					'parent' => 'kinsta-cache',
				)
			);

			$wp_admin_bar->add_node(
				array(
					'id' => 'kinsta-cache-object',
					'title' => 'Clear Object Cache',
					'href' => wp_nonce_url( admin_url( 'admin.php?page=kinsta-tools&clear-cache=kinsta-clear-cache-object' ), 'kinsta-clear-cache-admin-bar', 'kinsta_nonce' ),
					'parent' => 'kinsta-cache',
				)
			);
		} else {
			$wp_admin_bar->add_node(
				array(
					'id' => 'kinsta-cache',
					'title' => __( 'Clear Cache', 'kinsta-mu-plugins' ),
					'href' => wp_nonce_url( admin_url( 'admin.php?page=kinsta-tools&clear-cache=kinsta-clear-cache-full-page' ), 'kinsta-clear-cache-admin-bar', 'kinsta_nonce' ),
					'meta' => array( 'title' => __( 'Clear Cache', 'kinsta-mu-plugins' ) ),
					'parent' => 'top-secondary',
				)
			);
		}
	}

	/**
	 * Load the menu page.
	 *
	 * @return void
	 */
	public function admin_menu_page() {
		include plugin_dir_path( __FILE__ ) . 'pages/pages.php';
	}

	/**
	 * Show Kinsta menu icon.
	 *
	 * @return void
	 */
	public function menu_icon_style() {
		?>
	<style>
	#adminmenu .toplevel_page_kinsta-tools .wp-menu-image {
		background-repeat:no-repeat;
		background-position: 50% -28px;
		background-image: url( '<?php echo esc_url( Shared::shared_resource_url() ); ?>shared/images/menu-icon.svg' )
	}
	#adminmenu .toplevel_page_kinsta-tools:hover .wp-menu-image,  #adminmenu .toplevel_page_kinsta-tools.wp-has-current-submenu .wp-menu-image, #adminmenu .toplevel_page_kinsta-tools.current .wp-menu-image {
		background-position: 50% 6px;
	}
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
		if ( ! empty( $_GET['kinsta-cache-cleared'] ) && 'true' === $_GET['kinsta-cache-cleared'] ) :
			?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Cache cleared successfully', 'kinsta-mu-plugins' ); ?></p>
		</div>
			<?php
		endif;
	}

	/**
	 * AJAX Action to save custom path
	 *
	 * @return void
	 */
	public function action_kinsta_save_custom_path() {
		check_ajax_referer( 'save_plugin_options', 'kinsta_nonce' );

		$paths = get_option( 'kinsta-cache-additional-paths' );
		if ( empty( $paths ) ) {
			$paths = array();
		}

		$paths[] = array(
			'path' => sanitize_text_field( $_POST['path'] ),
			'type' => sanitize_text_field( wp_unslash( $_POST['type'] ) ),
		);
		$paths = array_values( $paths );

		update_option( 'kinsta-cache-additional-paths', $paths );

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

		$index = sanitize_text_field( wp_unslash( $_POST['index'] ) );
		$paths = get_option( 'kinsta-cache-additional-paths' );
		if ( ! empty( $paths[ $index ] ) ) {
			unset( $paths[ $index ] );
		}

		if ( count( $paths ) === 0 ) {
			delete_option( 'kinsta-cache-additional-paths' );
		} else {
			$paths = array_values( $paths );
			update_option( 'kinsta-cache-additional-paths', $paths );
		}

		die();
	}

	/**
	 * Sets the required capability to view and use the cache purging options.
	 *
	 * @return  string the required capability
	 */
	private function set_view_role_or_capability() {
		if ( defined( 'KINSTAMU_ROLE' ) && is_string( KINSTAMU_ROLE ) ) {
			return esc_attr( KINSTAMU_ROLE );
		}
		return 'manage_options';
	}

}
