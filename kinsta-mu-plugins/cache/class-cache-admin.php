<?php
/**
 * Kinsta Cache Admin classes
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
 * Cache_Admin class
 *
 * @since 1.0.0
 */
class Cache_Admin {

	/**
	 * Kinsta/Cache Object
	 *
	 * @var object
	 */
	public $kinsta_cache;

	/**
	 * Kinsta/Cache_Purge Object
	 *
	 * @var object
	 */
	public $kinsta_cache_purge;

	/**
	 * The role or capability to view and use cache options
	 *
	 * @var string
	 */
	private $view_role_or_capability;

	/**
	 * Constructor class
	 *
	 * @param object $kinsta_cache Kinsta Cache Object.
	 */
	public function __construct( $kinsta_cache ) {

		$this->kinsta_cache = $kinsta_cache;
		$this->kinsta_cache_purge = $this->kinsta_cache->kinsta_cache_purge;
		$this->view_role_or_capability = $this->set_view_role_or_capability();

		add_action( 'admin_menu', array( $this, 'admin_menu_item' ) );
		if ( KINSTAMU_WHITELABEL === false ) {
			add_action( 'admin_head', array( $this, 'menu_icon_style' ) );
		}
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_item' ), 100 );
	}

	/**
	 * Add main Kinsta Tools menu item.
	 */
	public function admin_menu_item() {

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
	 * Add Admin bar menu
	 *
	 * @param  object $wp_admin_bar WP_Admin_Bar object.
	 * @return void
	 */
	public function admin_bar_item( $wp_admin_bar ) {
		if ( ! current_user_can( $this->view_role_or_capability ) ) {
			return;
		}

		if ( $this->kinsta_cache->has_object_cache ) {

			$wp_admin_bar->add_node(
				array(
					'id' => 'kinsta-cache',
					'title' => __( 'Clear Cache', 'kinsta-mu-plugins' ),
					'meta' => array( 'title' => __( 'Clear Cache', 'kinsta-mu-plugins' ) ),
					'parent' => 'top-secondary',
				)
			);

			$wp_admin_bar->add_node(
				array(
					'id' => 'kinsta-cache-all',
					'title' => 'Clear All Caches',
					'href' => wp_nonce_url( admin_url( 'admin-ajax.php?action=kinsta_clear_cache_all&source=adminbar' ), 'kinsta-clear-cache-all', 'kinsta_nonce' ),
					'parent' => 'kinsta-cache',
				)
			);

			$wp_admin_bar->add_node(
				array(
					'id' => 'kinsta-cache-full-page',
					'title' => 'Clear Full Page Cache',
					'href' => wp_nonce_url( admin_url( 'admin-ajax.php?action=kinsta_clear_cache_full_page&source=adminbar' ), 'kinsta-clear-cache-full-page', 'kinsta_nonce' ),
					'parent' => 'kinsta-cache',
				)
			);

			$wp_admin_bar->add_node(
				array(
					'id' => 'kinsta-cache-object',
					'title' => 'Clear Object Cache',
					'href' => wp_nonce_url( admin_url( 'admin-ajax.php?action=kinsta_clear_cache_object&source=adminbar' ), 'kinsta-clear-cache-object', 'kinsta_nonce' ),
					'parent' => 'kinsta-cache',
				)
			);
		} else {
			$wp_admin_bar->add_node(
				array(
					'id' => 'kinsta-cache',
					'title' => __( 'Clear Cache', 'kinsta-mu-plugins' ),
					'href' => wp_nonce_url( admin_url( 'admin-ajax.php?action=kinsta_clear_cache_full_page&source=adminbar' ), 'kinsta-clear-cache-full-page', 'kinsta_nonce' ),
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

		// TODO: Revisit & test the $_POST.
		if ( ! empty( $_POST ) ) { // WPCS: CSRF ok.
			$this->kinsta_cache->save_plugin_options();
		}
		include plugin_dir_path( __FILE__ ) . 'pages/pages.php';
	}

	/**
	 * Show Kinsta menu icon
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
	 * Sets the required capability to view and use the cache purging options
	 *
	 * @return  string the required capability or role
	 */
	private function set_view_role_or_capability() {

		if ( defined( 'KINSTAMU_ROLE' ) && is_string( KINSTAMU_ROLE ) ) {
			return esc_attr( KINSTAMU_ROLE );
		}

		return 'manage_options';
	}
}
