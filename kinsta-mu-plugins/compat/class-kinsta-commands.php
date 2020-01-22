<?php
/**
 * Compat: Kinsta_Commands class
 *
 * @package KinstaMUPlugins/Compat
 */

namespace Kinsta\Compat;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

use WP_CLI;

use Kinsta\Compat\WP_CLI\Plugin_List_Command;
use Kinsta\Compat\WP_CLI\Cache_Purge_Command;

/**
 * Class to handle WP CLI custom commands registrations.
 */
class Kinsta_Commands {

	/**
	 * The Banned_Plugins class instance.
	 *
	 * This instance will provide access to the plugins registered on the
	 * Banned list.
	 *
	 * @var \Kinsta\Compat\Banned_Plugins
	 */
	private $banned_plugins;

	/**
	 * Registere a new commands to WP CLI.
	 *
	 * @return void
	 */
	public function add_commands() {
		if ( ! class_exists( 'WP_CLI' ) || ! class_exists( 'WP_CLI_Command' ) ) {
			return;
		}

		require_once plugin_dir_path( __FILE__ ) . 'wp-cli/class-plugin-list-command.php';
		require_once plugin_dir_path( __FILE__ ) . 'wp-cli/class-cache-purge-command.php';

		$banned_plugins = $this->get_banned_plugins();

		if ( $this->banned_plugins instanceof Banned_Plugins ) {
			$banned_plugins_args = [
				'banned_list'   => $banned_plugins->get_banned_list(),
				'warning_list'  => $banned_plugins->get_warning_list(),
				'disabled_list' => $banned_plugins->get_disabled_list(),
			];
			WP_CLI::add_command( 'kinsta plugin list', new Plugin_List_Command( $banned_plugins_args ) );
		}

		WP_CLI::add_command( 'kinsta cache purge', new Cache_Purge_Command() );
	}

	/**
	 * Register the Banned_Plugins class instance.
	 *
	 * @param \Kinsta\Compat\Banned_Plugins $banned_plugins The class instance.
	 * @return void
	 */
	public function set_banned_plugins( \Kinsta\Compat\Banned_Plugins $banned_plugins ) {
		$this->banned_plugins = $banned_plugins;
	}

	/**
	 * Get the Banned_Plugins class instance.
	 *
	 * @return \Kinsta\Compat\Banned_Plugins
	 */
	public function get_banned_plugins() {
		return $this->banned_plugins;
	}
}
