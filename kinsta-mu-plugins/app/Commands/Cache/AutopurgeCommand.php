<?php
/**
 * Compat: WP_CLI class
 *
 * @package KinstaMUPlugins/Compat
 */

namespace Kinsta\KMP\Commands\Cache;

use Kinsta\KMP\Cache\Autopurge;
use Kinsta\KMP\Contracts\Autopurgable;
use WP_CLI;
use WP_CLI_Command;

class AutopurgeCommand extends WP_CLI_Command {

    private Autopurge $autopurge;

    public function __construct(Autopurge $autopurge)
    {
        $this->autopurge = $autopurge;
    }

	/**
	 * Check the current global autopurge status.
	 *
     * ## EXAMPLES
	 *
	 *     # Check the current global autopurge status.
	 *     $ wp kinsta cache autopurge status
	 *     Success: Autopurge is enabled.
     *
	 * @param array $args The command arguments. Unused.
     * @param array $assoc_args The command associative arguments. Unused.
	 * @return void
	 */
	public function status( array $args, array $assoc_args ) {
        WP_CLI::log(
            $this->autopurge->status() === 'enabled' ?
            __( 'Autopurge is enabled.', 'kinsta-mu-plugins' ) :
            __( 'Autopurge is disabled.', 'kinsta-mu-plugins' )
        );
	}

	/**
	 * Disable global autopurge.
	 *
     * ## EXAMPLES
	 *
	 *     # Disable the global autopurge.
     *     $ wp kinsta cache autopurge disable
	 *     Success: Autopurge is now disabled.
     *
	 * @param array $args The command arguments. Unused.
     * @param array $assoc_args The command associative arguments. Unused.
	 * @return void
	 */
	public function disable( array $args, array $assoc_args ) {
        if ($this->autopurge->status() === 'disabled') {
            WP_CLI::warning( __( 'Autopurge is already disabled.', 'kinsta-mu-plugins' ) );
            return;
        }

        if ($this->autopurge->disable()) {
            WP_CLI::success( __( 'Autopurge is now disabled.', 'kinsta-mu-plugins' ) );
        } else {
            WP_CLI::warning( __( 'Failed to disable autopurge.', 'kinsta-mu-plugins' ) );
        }
	}

	/**
	 * Enable global autopurge.
	 *
     * ## EXAMPLES
	 *
	 *     # Enable global autopurge.
     *     $ wp kinsta cache autopurge enable
	 *     Success: Autopurge is now enabled.
     *
	 * @param array $args The command arguments. Unused.
     * @param array $assoc_args The command associative arguments. Unused.
	 * @return void
	 */
	public function enable( array $args, array $assoc_args ) {
        if ($this->autopurge->status() === 'enabled') {
            WP_CLI::warning( __( 'Autopurge is already enabled.', 'kinsta-mu-plugins' ) );
            return;
        }

        if ($this->autopurge->enable()) {
            WP_CLI::success( __( 'Autopurge is now enabled.', 'kinsta-mu-plugins' ) );
        } else {
            WP_CLI::warning( __( 'Failed to enable autopurge.', 'kinsta-mu-plugins' ) );
        }
	}

    /**
	 * Check the current autopurge status.
	 *
     * ## OPTIONS
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
	 *     # Check the current autopurge list.
	 *     $ wp kinsta cache autopurge list
	 *     +----------------------+-------------------------------------------------------------+---------+
	 *     | name                 | description                                                 | status  |
	 *     +----------------------+-------------------------------------------------------------+---------+
	 *     | wp_option_controller | Purge cache when options are updated.                       | on      |
	 *     | elementor_controller | Purge cache on Elementor updates that affect the front-end. | off     |
	 *     +----------------------+-------------------------------------------------------------+---------+
	 *
	 * @param array $args The command arguments.
     * @param array $assoc_args The command associative arguments.
	 * @return void
	 */
	public function list( array $args, array $assoc_args ) {
        $list = [];
        $format = $assoc_args['format'] ?? 'table';

        foreach ( $this->autopurge as $item ) {
            if ($item instanceof Autopurgable) {
                $list[] = [
                    'name'        => $item->getName(),
                    'description' => $item->getDescription(),
                    'status'      => $item->isOn() ? 'on' : 'off',
                ];
            }
        }

        WP_CLI\Utils\format_items( $format, $list, [ 'name', 'description', 'status' ] );
	}

    /**
	 * Check the current autopurge status.
	 *
	 * ## EXAMPLES
	 *
	 *     # Enable an autopurge setting.
	 *     $ wp kinsta cache autopurge toggle wp_option_controller on
     *     Success: Autopurge setting updated.
     *
	 *     # Enable or disable an autopurge setting.
	 *     $ wp kinsta cache autopurge toggle wp_option_controller on
     *     Success: Autopurge setting updated.
	 *
	 * @param array $args The command arguments.
     * @param array $assoc_args The command associative arguments.
	 * @return void
	 */
	public function toggle( array $args, array $assoc_args ) {
        if (!isset($args[0])) {
            WP_CLI::error( __( 'Please provide the setting to update.', 'kinsta-mu-plugins' ) );

            return;
        }

        if (!isset($args[1])) {
            WP_CLI::error( __( 'Please provide the status to update on the setting.', 'kinsta-mu-plugins' ) );

            return;
        }

        $key = $args[0];
        $status = $args[1];

        if ($status !== 'on' && $status !== 'off') {
            WP_CLI::error( __( 'Status value is invalid. Please use "on" or "off".', 'kinsta-mu-plugins' ) );
            return;
        }

        $status = $status === 'on' ? true : false;

        try {
            $updated = $this->autopurge->update($key, $status);

            if ($updated) {
                WP_CLI::success( __( 'Autopurge setting updated.', 'kinsta-mu-plugins' ) );
            } else {
                WP_CLI::warning( __( 'Autopurge setting is not updated.', 'kinsta-mu-plugins' ) );
            }
        } catch (\Throwable $th) {
            WP_CLI::error( $th->getMessage() );
        }
	}
}
