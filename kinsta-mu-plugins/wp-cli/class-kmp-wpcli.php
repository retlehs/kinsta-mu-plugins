<?php
/**
 * Compat: Kinsta_Commands class
 *
 * @package KinstaMUPlugins/Compat
 */

namespace Kinsta;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

use Kinsta\KMP\Cache\Autopurge;
use Kinsta\KMP\Commands\Cache;
use Kinsta\KMP\Commands\Cache\AutopurgeList;
use Kinsta\KMP\Commands\Cache\AutopurgeSet;
use WP_CLI;
use Kinsta\WP_CLI\Plugin_List_Command;
use Kinsta\WP_CLI\Cache_Purge_Command;

/**
 * Class to handle WP CLI custom commands registrations.
 */
class KMP_WPCLI {

	/**
	 * The kmp class.
	 *
	 * @var \Kinsta\KMP
	 */
	public $kmp;

	/**
	 * The Banned_Plugins class instance.
	 *
	 * This instance will provide access to the plugins registered on the
	 * Banned list.
	 *
	 * @var \Kinsta\Security\Banned_Plugins
	 */
	private $banned_plugins;

	/**
	 * The Constructor.
	 *
	 * @param \Kinsta\KMP $kmp The KMP object.
	 */
	public function __construct( \Kinsta\KMP $kmp, Autopurge $autopurge ) {
		$this->kmp = $kmp;
		$this->banned_plugins = $kmp->banned_plugins;

		if ( ! class_exists( 'WP_CLI' ) || ! class_exists( 'WP_CLI_Command' ) ) {
			return;
		}

		require_once plugin_dir_path( __FILE__ ) . 'commands/class-plugin-list-command.php';
		require_once plugin_dir_path( __FILE__ ) . 'commands/class-cache-purge-command.php';

		$banned_plugins_args = array(
			'banned_list'   => $this->banned_plugins->get_banned_list(),
			'warning_list'  => $this->banned_plugins->get_warning_list(),
			'disabled_list' => $this->banned_plugins->get_disabled_list(),
		);
		WP_CLI::add_command( 'kinsta plugin list', new Plugin_List_Command( $banned_plugins_args ) );
		WP_CLI::add_command( 'kinsta cache purge', new Cache_Purge_Command( $this->kmp->kinsta_cache_purge ) );
		WP_CLI::add_command( 'kinsta cache autopurge', new Cache\AutopurgeCommand( $autopurge ) );
		WP_CLI::add_hook(
			'after_invoke:cache flush',
			function () {
				( new Cache_Purge_Command( $this->kmp->kinsta_cache_purge ) )(
					array(),
					array( 'all' => true )
				);
			}
		);
	}
}
