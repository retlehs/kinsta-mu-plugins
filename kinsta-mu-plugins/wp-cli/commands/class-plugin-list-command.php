<?php
/**
 * Compat: WP_CLI class
 *
 * @package KinstaMUPlugins/Compat
 */

namespace Kinsta\WP_CLI;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

use WP_CLI;
use WP_CLI_Command;

/**
 * Class to register to register a custom Kinsta WP-CLI command .
 */
class Plugin_List_Command extends WP_CLI_Command {

	/**
	 * List of plugins installed on the site.
	 *
	 * @var array
	 */
	private $installed_plugins = array();

	/**
	 * List of plugins that we encourage our users to deactivate.
	 *
	 * @var array
	 */
	private $warning_plugins = array();

	/**
	 * List of plugins that will be forcibly disabled.
	 *
	 * @var array
	 */
	private $disabled_plugins = array();

	/**
	 * List of plugins in the Banned category.
	 *
	 * @var array
	 */
	private $banned_plugins = array();

	/**
	 * List of plugins with updates.
	 *
	 * @var array
	 */
	private $update_plugins = array();

	/**
	 * The Constructor.
	 *
	 * @param array $args Arguments to pass additional information needed in the command line.
	 */
	public function __construct( $args ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$args = wp_parse_args(
			$args,
			array(
				'warning_list' => array(),
				'disabled_list' => array(),
				'banned_list' => array(),
			)
		);

		$this->installed_plugins = get_plugins();
		$this->update_plugins = get_site_transient( 'update_plugins' );

		$this->warning_plugins  = $args['warning_list'];
		$this->disabled_plugins = $args['disabled_list'];
		$this->banned_plugins = $args['banned_list'];
	}

	/**
	 * Gets a list of plugins.
	 *
	 * Displays a list of the plugins installed on the site with activation
	 * status, whether or not there's an update available, etc.
	 *
	 * Use `--status=active` to list installed active plugins.
	 *
	 * ## OPTIONS
	 *
	 * [--status=<status>]
	 * : Render output based on the plugin status.
	 * ---
	 * default: all
	 * options:
	 *   - all
	 *   - active
	 *   - inactive
	 *   - banned
	 * ---
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - count
	 *   - json
	 *   - yaml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     # List active plugins on the site.
	 *     $ wp kinsta plugin list --status=active --format=json
	 *     [{"name":"dynamic-hostname","status":"active","update":"none","version":"0.4.2"},{"name":"tinymce-templates","status":"active","update":"none","version":"4.4.3"},{"name":"wp-multibyte-patch","status":"active","update":"none","version":"2.4"},{"name":"wp-total-hacks","status":"active","update":"none","version":"2.0.1"}]
	 *
	 * @subcommand list
	 *
	 * @param array $args The command arguments.
	 * @param array $assoc_args The command associative arguments e.g. --format=json.
	 * @return void
	 */
	public function __invoke( $args, $assoc_args ) {

		$format = isset( $assoc_args['format'] ) ? $assoc_args['format'] : 'table';
		$status = isset( $assoc_args['status'] ) ? $assoc_args['status'] : 'all';

		$plugins = $this->get_plugins(
			array(
				'status' => $status,
			)
		);

		WP_CLI\Utils\format_items( $format, $plugins, array( 'slug', 'name', 'status', 'update', 'banned', 'version' ) );
	}

	/**
	 * Retrieve the plugin list.
	 *
	 * @param array $args Plugins arguments.
	 * @return array
	 */
	private function get_plugins( $args = array() ) {

		$plugins = array();
		$status = $args['status'];

		foreach ( $this->installed_plugins as $plugin_file => $data ) {

			$plugin_status = $this->get_status( $plugin_file );
			$plugin_banned = $this->is_banned( $plugin_file );

			if ( in_array( $status, array( 'active', 'inactive' ), true ) && $status !== $plugin_status ) {
				continue;
			}

			if ( 'banned' === $status && 'no' === $plugin_banned ) {
				continue;
			}

			$plugins[] = array(
				'slug'    => self::parse_plugin_slug( $plugin_file ),
				'name'    => $this->installed_plugins[ $plugin_file ]['Name'],
				'status'  => $plugin_status,
				'banned'  => $plugin_banned,
				'update'  => $this->is_update_available( $plugin_file ),
				'version' => $this->installed_plugins[ $plugin_file ]['Version'],
			);
		}

		return $plugins;
	}

	/**
	 * Retrieve the plugin status.
	 *
	 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
	 * @return string
	 */
	private function get_status( $plugin_file ) {

		if ( is_plugin_active_for_network( $plugin_file ) ) {
			return 'active-network';
		}

		if ( is_plugin_active( $plugin_file ) ) {
			return 'active';
		}

		return 'inactive';
	}

	/**
	 * Retrieve status if the plugin is banned.
	 *
	 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
	 * @return string
	 */
	private function is_banned( $plugin_file ) {
		return in_array( $plugin_file, $this->banned_plugins, true ) ? 'yes' : 'no';
	}

	/**
	 * Retrieve plugin st
	 *
	 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
	 * @return string
	 */
	private function is_update_available( $plugin_file ) {
		return array_key_exists( $plugin_file, $this->update_plugins->response ) ? 'available' : 'none';
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
}
