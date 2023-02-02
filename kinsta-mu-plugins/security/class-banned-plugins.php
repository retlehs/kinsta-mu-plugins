<?php
/**
 * Compat: Disable_Plugins class
 *
 * @package KinstaMUPlugins/Compat
 */

namespace Kinsta;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

/**
 * Class to disable banned plugins on the list.
 */
class Banned_Plugins {

	/**
	 * List of plugins that we encourage our users to deactivate.
	 *
	 * @var array
	 */
	private $warning_list = array(
		'all-in-one-wp-migration/all-in-one-wp-migration.php',
		'allow-php-execute/allow-php-execute.php',
		'cache-enabler/cache-enabler.php',
		'dynamic-widgets/dynamic-widgets.php',
		'inactive-user-deleter/inactive-user-deleter.php',
		'jch-optimize/jch-optimize.php',
		'p3-profiler/p3-profiler.php',
		'pagefrog/pagefrog.php',
		'rvg-optimize-database/rvg-optimize-database.php',
		'updraft/updraft.php',
		'wonderm00ns-simple-facebook-open-graph-tags/wonderm00n-open-graph.php',
		'wordpress-gzip-compression/ezgz.php',
		'wordpress-popular-posts/wordpress-popular-posts.php',
		'wp-db-backup/wp-db-backup.php',
		'wp-db-backup-made/wpa-wp.php',
		'bwp-minify/bwp-minify.php',
		'exec-php/exec-php.php',
		'loginwall/loginwall.php',
		'wp-rss-multi-importer/wp-rss-multi-importer.php',
		'wp-db-backup-made/wpa-wp.php',
		'duplicator-pro/duplicator-pro.php', // Duplicator Pro.
	);

	/**
	 * List of plugins that will be forcibly disabled.
	 *
	 * @var array
	 */
	private $disabled_list = array(
		'backupbuddy/backupbuddy.php',
		'snapshot/snapshot.php',
		'sg-cachepress/sg-cachepress.php',
		'litespeed-cache/litespeed-cache.php',
		'backwpup/backwpup.php',
		'backwpup-pro/backwpup.php',
		'p3/p3.php', // Pipdig Power Pack plugin.
	);

	/**
	 * List of plugins in the Banned category.
	 *
	 * @var array
	 */
	private $banned_list = array();

	/**
	 * Lists of active plugins on the site.
	 *
	 * @var array
	 */
	private $active_plugins = array();

	/**
	 * The Constructor.
	 * Sets up the options filter, and optionally handles an array of plugins to disable.
	 */
	public function __construct() {
		$this->check_server_banned_plugin_lists();

		$this->banned_list = array_merge( $this->warning_list, $this->disabled_list );  // Full list of Banned Plugins.
		$this->active_plugins = get_option( 'active_plugins', array() );
		global $wp_version;

		add_action( 'admin_init', array( $this, 'deactivate_disabled_plugins' ), PHP_INT_MAX );
		add_action( 'activated_plugin', array( $this, 'deactivate_disabled_plugin' ), PHP_INT_MAX );

		add_action( 'admin_print_scripts', array( $this, 'add_plugin_page_scripts' ), PHP_INT_MAX );
		add_action( 'admin_print_styles', array( $this, 'add_plugin_page_styles' ), PHP_INT_MAX );

		add_filter( 'plugin_install_action_links', array( $this, 'plugin_install_action_links' ), PHP_INT_MAX, 2 );
		add_filter( 'install_plugin_complete_actions', array( $this, 'install_plugin_complete_actions' ), PHP_INT_MAX, 3 );

		foreach ( $this->disabled_list as $plugin_file ) {
			add_filter( "plugin_action_links_{$plugin_file}", array( $this, 'disabled_plugin_action_links' ), PHP_INT_MAX );
		}

		foreach ( $this->warning_list as $plugin_file ) {
			add_filter( "plugin_action_links_{$plugin_file}", array( $this, 'warning_plugin_action_links' ), PHP_INT_MAX );
		}

		if ( self::shall_display_admin_notice() && version_compare( $wp_version, '4.3', '>' ) ) {
			add_action( 'admin_print_scripts', array( $this, 'add_notice_scripts' ), PHP_INT_MAX );
			add_action( 'admin_print_styles', array( $this, 'add_notice_styles' ), PHP_INT_MAX );
			add_action( 'admin_notices', array( $this, 'admin_notices' ), PHP_INT_MAX );
			add_action( 'wp_ajax_kinsta_dismiss_banned_plugins_nag', array( $this, 'dismiss_banned_plugins_nag' ), PHP_INT_MAX );
		}
	}

	/**
	 * Retrieve the plugins in the "Warning" list.
	 *
	 * @return array
	 */
	public function get_warning_list() {
		return $this->warning_list;
	}

	/**
	 * Retrieve the plugins in the "Disabled" list.
	 *
	 * @return array
	 */
	public function get_disabled_list() {
		return $this->disabled_list;
	}

	/**
	 * Retrieve the plugins in the "Banned" list.
	 *
	 * @return array
	 */
	public function get_banned_list() {
		return $this->banned_list;
	}

	/**
	 * Disaplay admin notice.
	 *
	 * @return void
	 */
	public function admin_notices() {

		$notice_content = $this->get_the_admin_notice_content();
		?>
		<div id="kinsta-banned-plugins-nag" class="notice notice-kinsta notice-error is-dismissible">
			<div class="notice-kinsta__content">
				<?php echo wp_kses_post( $notice_content ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Deactivate a single Disabled Plugin.
	 *
	 * @param string $active_plugin Path to the main plugin file from plugins directory.
	 * @return void
	 */
	public function deactivate_disabled_plugin( $active_plugin ) {
		if ( is_string( $active_plugin ) && ! empty( $active_plugin ) ) {
			if ( in_array( $active_plugin, $this->disabled_list, true ) ) {
				deactivate_plugins( $active_plugin );
			}
		}
	}

	/**
	 * Deactivate all Disabled Plugins at once
	 *
	 * @return void
	 */
	public function deactivate_disabled_plugins() {
		deactivate_plugins( $this->disabled_list ); // Deactivate all banned plugins at once.
	}

	/**
	 * Customize the Action links in the plugin table (e.g. Activate, Deactivate, etc.)
	 * for the plugins in the Warning List.
	 *
	 * @param array $actions An array of plugin action links. By default this can include 'activate', 'deactivate', and 'delete'.
	 * @return array The list of action links modfied.
	 */
	public function warning_plugin_action_links( $actions ) {

		/**
		 * Remove the activate link action.
		 *
		 * We're preventing plugin activation during installation (through WP.org Search or Plugin Uploads),
		 * so it does make more sense to disable the Activate link displayed ont the plugin Table List.
		 */
		unset( $actions['activate'] );

		$warning_actions = $actions;
		$banned_action_link = $this->get_banned_plugin_action_link();

		if ( is_string( $banned_action_link ) && ! empty( $banned_action_link ) ) {
			$warning_actions = array_merge( array( 'kinsta_banned' => $banned_action_link ), $warning_actions );
		}

		return $warning_actions;
	}

	/**
	 * Customize the Action links in the plugin table (e.g. Activate, Deactivate, etc.),
	 * for the plugins in the Disabled List.
	 *
	 * @param array $actions An array of plugin action links. By default this can include 'activate', 'deactivate', and 'delete'.
	 * @return array The list of action links modfied.
	 */
	public function disabled_plugin_action_links( $actions ) {

		$disabled_actions = array(
			'delete' => $actions['delete'],
		);

		$banned_action_link = $this->get_banned_plugin_action_link();
		if ( is_string( $banned_action_link ) && ! empty( $banned_action_link ) ) {
			$disabled_actions = array_merge( array( 'kinsta_banned' => $banned_action_link ), $disabled_actions );
		}

		return $disabled_actions;
	}

	/**
	 * Customize the action links for all the plugins in Banned List.
	 *
	 * @param array $action_links An array of plugin action links. Defaults are links to Details and Install Now.
	 * @param array $plugin       The plugin currently being listed.
	 * @return array
	 */
	public function plugin_install_action_links( $action_links, $plugin ) {

		foreach ( $this->banned_list as $banned ) {
			$banned_slug = self::parse_plugin_slug( $banned );
			if ( $plugin['slug'] === $banned_slug ) {

				$action_links = array(
					'<button type="button" class="button button-disabled" disabled="disabled">' . __( 'Banned', 'kinsta-mu-plugins' ) . '</button>',
				);

				$banned_action_link = $this->get_banned_plugin_install_action_link();
				if ( is_string( $banned_action_link ) && ! empty( $banned_action_link ) ) {
					$action_links = array_merge( $action_links, array( 'kinsta_banned_why' => $banned_action_link ) );
				}
			}
		}

		return $action_links;
	}

	/**
	 * Customize the action links for all the plugins in Banned List.
	 *
	 * @param array  $install_actions Array of plugin action links.
	 * @param object $api             Object containing WordPress.org API plugin data. Empty
	 *                                for non-API installs, such as when a plugin is installed
	 *                                via upload.
	 * @param string $plugin_file     Path to the plugin file relative to the plugins directory.
	 * @return array
	 */
	public function install_plugin_complete_actions( $install_actions, $api, $plugin_file ) {

		if ( in_array( $plugin_file, $this->banned_list, true ) ) {

			if ( isset( $install_actions['activate_plugin'] ) ) {
				unset( $install_actions['activate_plugin'] );
			}

			if ( isset( $install_actions['network_activate'] ) ) {
				unset( $install_actions['network_activate'] );
			}

			$install_actions = array(
				'kinsta_banned_plugin' => '<button type="button" class="button button-disabled" disabled="disabled">' . __( 'Banned', 'kinsta-mu-plugins' ) . '</button>',
			);

			$banned_action_link = $this->get_banned_plugin_install_action_link();
			if ( is_string( $banned_action_link ) && ! empty( $banned_action_link ) ) {
				$install_actions = array_merge( $install_actions, array( 'kinsta_banned_why' => $banned_action_link ) );
			}
		}

		return $install_actions;
	}

	/**
	 * Dismiss "Banned Plugins" notice
	 *
	 * @return void
	 */
	public function dismiss_banned_plugins_nag() {
		check_ajax_referer( 'kinsta-banned-plugins', 'nonce' );
		set_transient( 'kinsta_dismiss_banned_plugins_nag', '1', MONTH_IN_SECONDS );
		wp_die();
	}

	/**
	 * Get the notice to disapled in the.
	 *
	 * @return string
	 */
	private function get_notifiable_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$installed_plugins = get_plugins();
		$active_banned_plugins = array_intersect( (array) $this->active_plugins, (array) $this->banned_list );

		$notifiable_plugins = array();
		foreach ( $active_banned_plugins as $plugin_file ) {
			if ( array_key_exists( $plugin_file, $installed_plugins ) ) {
				$notifiable_plugins[ $plugin_file ] = $installed_plugins[ $plugin_file ];
			}
		}

		return array_column( $notifiable_plugins, 'Name' );
	}

	/**
	 * Retrieve the content message to display in the admin notice.
	 *
	 * @return string
	 */
	private function get_the_admin_notice_content() {

		$notifiable_plugins = $this->get_notifiable_plugins();

		$heading = _n( 'Kinsta detected a banned plugin', 'Kinsta detected banned plugins', count( $notifiable_plugins ), 'kinsta-mu-plugins' );

		// Translators: 1st %s the "heading" (e.g. Kinsta detected a banned plugin), 2nd %s plugin names.
		$notice_content = '<p>' . sprintf( '<strong>%s</strong>: %s.', $heading, implode( ', ', $notifiable_plugins ) ) . '</p>';

		// Translators: %s "this plugin" if singular, "these plugins" if plural.
		$notice_content .= '<p>' . sprintf( __( 'Please deactivate %s as soon as possible. Using a banned plugin can cause performance issues for your site or compatibility issues with our hosting platform.', 'kinsta-mu-plugins' ), _n( 'this plugin', 'these plugins', count( $notifiable_plugins ), 'kinsta-mu-plugins' ) ) . '</p>';

		// The button.
		$notice_content .= '<p><a class="button action" href="https://kinsta.com/knowledgebase/banned-plugins/" target="_blank">' . __( 'Learn more about banned plugins', 'kinsta-mu-plugins' ) . '<span class="dashicons dashicons-external"></span></a></p>';

		// Set the default string when KINSTAMU_WHITELABLE is enabled.
		if ( is_whitelabel_enabled() ) {

			$heading = _n( 'Banned plugin detected', 'Banned plugins detected', count( $notifiable_plugins ), 'kinsta-mu-plugins' );

			// Translators: 1st %s the "heading" (e.g. Kinsta detected a banned plugin), 2nd %s plugin names.
			$notice_content = '<p>' . sprintf( '<strong>%s</strong>: %s.', $heading, implode( ', ', $notifiable_plugins ) ) . '</p>';

			$notice_content .= '<p>' . __( 'Using a banned plugin can cause performance issues for your site or compatibility issues with the hosting platform.', 'kinsta-mu-plugins' ) . '</p>';

			// Translators: %s "this plugin" if singular, "these plugins" if plural.
			$notice_content .= '<p>' . sprintf( __( 'Please deactivate %s as soon as possible.', 'kinsta-mu-plugins' ), _n( 'this plugin', 'these plugins', count( $notifiable_plugins ), 'kinsta-mu-plugins' ) ) . '</p>';
		}

		return apply_filters( 'kinsta_banned_plugin_notice_content', $notice_content, $notifiable_plugins );
	}

	/**
	 * Retrieve the plugin action link shown on the plugin table list.
	 *
	 * @return string
	 */
	public function get_banned_plugin_action_link() {

		$banned_action = '<a class="kinsta-plugin-action kinsta-plugin-action--banned" href="https://kinsta.com/knowledgebase/banned-plugins/" target="_blank">' . __( 'Banned', 'kinsta-mu-plugins' ) . ' <span class="dashicons dashicons-external"></span></a>';

		if ( is_whitelabel_enabled() ) {
			$banned_action = '<span class="kinsta-plugin-action kinsta-plugin-action--banned">' . __( 'Banned', 'kinsta-mu-plugins' ) . '</span>';
		}

		return apply_filters( 'kinsta_banned_plugin_action_link', $banned_action );
	}

	/**
	 * Retrieve the action link when installing the plugin.
	 *
	 * @return string
	 */
	public function get_banned_plugin_install_action_link() {

		$banned_action = '<a href="https://kinsta.com/knowledgebase/banned-plugins/" target="_blank">' . __( 'Why?', 'kinsta-mu-plugins' ) . '</a>';
		if ( is_whitelabel_enabled() ) {
			$banned_action = '';
		}

		return apply_filters( 'kinsta_banned_plugin_install_action_link', $banned_action );
	}

	/**
	 * Get the styles to customize the plugin table list.
	 *
	 * @return string
	 */
	private function get_list_tables_styles() {
		$active_banned_plugins = array_intersect( (array) $this->active_plugins, (array) $this->banned_list );

		ob_start();
		?>
		.plugins .kinsta_banned {
			color: #999;
		}
		.plugins .kinsta-plugin-action--banned .dashicons {
			float: none;
			width: 16px;
			height: 16px;
			padding: 0;
			display: inline;
			position: relative;
			top: 3px;
		}
		.plugins .kinsta-plugin-action--banned .dashicons:before {
			color: inherit;
			background: none;
			box-shadow: none;
			font-size: 16px;
			padding: 0;
		}
		.kinsta-banned-plugin .kinsta-banned-plugin__title {
			color: #0073aa;
		}

		<?php
		$last_plugin = end( $active_banned_plugins );
		foreach ( $active_banned_plugins as $plugin ) :
			$plugin_slug = self::parse_plugin_slug( $plugin );
			$last_th = $plugin === $last_plugin ? 'th' : 'th,';
			?>
		.plugins .active[data-slug="<?php echo esc_attr( $plugin_slug ); ?>"] td,
		.plugins .active[data-slug="<?php echo esc_attr( $plugin_slug ); ?>"] <?php echo esc_html( $last_th ); ?>
		<?php endforeach; ?>
		{
			background: #fef7f7 !important;
		}

		<?php
		foreach ( $active_banned_plugins as $plugin ) :
			$plugin_slug = self::parse_plugin_slug( $plugin );
			$last_th = $plugin === $last_plugin ? 'th.check-column' : 'th.check-column,';
			?>
		.plugin-update-tr.active[data-slug="<?php echo esc_attr( $plugin_slug ); ?>"] td,
		.plugins .active[data-slug="<?php echo esc_attr( $plugin_slug ); ?>"] <?php echo esc_html( $last_th ); ?>
		<?php endforeach; ?>
		{
			border-left: 4px solid #dc3232;
		}
		<?php
		$styles = ob_get_contents();
		ob_end_clean();
		return $styles;
	}

	/**
	 * Print the scripts on the Plugins Admin page.
	 *
	 * @return void
	 */
	public function add_plugin_page_scripts() {

		if ( self::is_admin_plugin_page() ) {

			$warning_slugs = array();
			foreach ( $this->warning_list as $warning ) {
				$warning_slugs[] = self::parse_plugin_slug( $warning );
			}

			$warning_data = 'var kinstaWarningPlugins = ' . json_encode( $warning_slugs ) . ';';

			$disabled_slugs = array();
			foreach ( $this->disabled_list as $disabled ) {
				$disabled_slugs[] = self::parse_plugin_slug( $disabled );
			}

			$disabled_data = 'var kinstaDisabledPlugins = ' . json_encode( $disabled_slugs ) . ';';

			echo '<script type="text/javascript">' . wp_kses( $warning_data . $disabled_data, array() ) . '</script>';
		}
	}

	/**
	 * Print the styles on the Plugins Admin page.
	 *
	 * @return void
	 */
	public function add_plugin_page_styles() {

		if ( self::is_admin_plugin_page() ) {
			$styles = $this->get_list_tables_styles();
			echo '<style type="text/css">' . wp_kses( $styles, array() ) . '</style>';
		}
	}

	/**
	 * Print the styles for the dismissable notice.
	 *
	 * @return void
	 */
	public function add_notice_styles() {
		?>
	<style type="text/css">
	.notice-kinsta__content {
		padding: 1em 0;
	}
	.notice-kinsta__content p {
		margin: 0 0 2px;
		padding: 0;
	}
	.notice-kinsta__content p:last-child {
		margin: 0;
	}
	.notice-kinsta__content .button {
		margin-top: 8px;
	}
	.notice-kinsta__content .button .dashicons {
		position: relative;
		top: 5px;
		margin-left: 5px;
	}
	.notice-kinsta__content .button .dashicons,
	.notice-kinsta__content .button .dashicons-before:before {
		width: 16px;
		height: 16px;
		font-size: 16px;
	}
	</style>
		<?php
	}

	/**
	 * Print the scripts for the dismissable notice.
	 *
	 * @return void
	 */
	public function add_notice_scripts() {
		?>
	<script type="text/javascript">
	if ( typeof jQuery !== 'undefined' ) {
		jQuery( function( $ ) {
			$( '#wpbody-content' ).on( 'click', '#kinsta-banned-plugins-nag .notice-dismiss', function() {
				$.post( ajaxurl, {
					nonce: '<?php echo esc_attr( wp_create_nonce( 'kinsta-banned-plugins' ) ); ?>',
					action: 'kinsta_dismiss_banned_plugins_nag'
				}, function( response ) {
					console.log( 'Dismissed!' );
				});
			});
		});
	}
	</script>
		<?php
	}

	/**
	 * Whether admin notice should be displayed.
	 *
	 * @return bool
	 */
	private function shall_display_admin_notice() {

		$shall = false;
		$dismissed = get_transient( 'kinsta_dismiss_banned_plugins_nag' );
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$installed_plugins = get_plugins();
		$active_banned_plugins = array_intersect( (array) $this->active_plugins, (array) $this->banned_list );

		$active_banned_plugin_count = 0;
		foreach ( $active_banned_plugins as $plugin_file ) {
			if ( array_key_exists( $plugin_file, $installed_plugins ) ) {
				$active_banned_plugin_count += 1;
			}
		}

		if ( current_user_can( 'activate_plugins' ) && 0 < $active_banned_plugin_count && 1 !== absint( $dismissed ) ) {
			$shall = true;
		}

		return $shall;
	}

	/**
	 * Parse the plugin slug from the plugin basename.
	 *
	 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
	 * @return string
	 */
	private static function parse_plugin_slug( $plugin_file ) {
		$parts = explode( '/', $plugin_file );
		return isset( $parts[0] ) ? $parts[0] : '';
	}

	/**
	 * Check if we are on the plugin page.
	 *
	 * @return bool
	 */
	private static function is_admin_plugin_page() {
		$current_screen = function_exists( 'get_current_screen' ) ? \get_current_screen() : null;
		if ( ! is_object( $current_screen ) ) {
			return false;
		}

		return isset( $current_screen->base ) && ( 'plugins' === $current_screen->base || 'plugin-install' === $current_screen->base );
	}

	/**
	 * Updates the banned plugins list from the server env.
	 *
	 * @return void
	 */
	private function check_server_banned_plugin_lists() {

		if ( isset( $_SERVER ) && isset( $_SERVER['KINSTA_BANNED_PLUGINS'] ) ) {
			$banned_plugins_array = json_decode( $_SERVER['KINSTA_BANNED_PLUGINS'], $assoc_array = true );
			if ( is_array( $banned_plugins_array ) && isset( $banned_plugins_array['disabled'] ) && isset( $banned_plugins_array['warning'] ) ) {

				if ( is_array( $banned_plugins_array['disabled'] ) ) {
					$this->disabled_list = $banned_plugins_array['disabled'];
				}

				if ( is_array( $banned_plugins_array['warning'] ) ) {
					$this->warning_list = $banned_plugins_array['warning'];
				}
			}
		}
	}
}
