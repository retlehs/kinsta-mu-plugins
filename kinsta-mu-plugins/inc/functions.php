<?php

namespace Kinsta\KMP;

use Kinsta\KMP\Helpers\Whitelabel;

use function defined;
use function file_put_contents;
use function gmdate;
use function json_encode;
use function sprintf;

use const FILE_APPEND;

/**
 * Retrieve the instance for Whitelabel settings.
 *
 * @internal This function is for internal use only. Other plugins should not use this function directly.
 */
function whitelabel(): Whitelabel
{
	static $instance = null;

	if ($instance === null) {
		$instance = new Whitelabel(KINSTAMU_WHITELABEL);
	}

	return $instance;
}

/**
 * A helper function to check if the Whitelable is enabled.
 */
function is_whitelabel_enabled(): bool
{
	return whitelabel()->isEnabled();
}

/**
 * Check whether the global autopurge is enabled.
 */
function is_autopurge_enabled(): bool
{
	if (defined('KINSTAMU_DISABLE_AUTOPURGE') && KINSTAMU_DISABLE_AUTOPURGE === true) {
		return false;
	}

	$status = get_option('kinsta-autopurge-status', null);

	return $status === 'enabled' || $status === null;
}

/**
 * Log debug messages in the PHP error log if KINSTAMU_DEBUG_LOG is enabled.
 *
 * @param array<string,mixed> $context
 *
 * @internal This function is for internal use only. Other plugins should not use this function directly.
 */
function debug_log(string $message, array $context = []): void
{
	/**
	 * Note: KINSTAMU_DEBUG_LOG is currently experimental and may be renamed in the future.
	 */
	if (! defined('KINSTAMU_DEBUG_LOG') || KINSTAMU_DEBUG_LOG !== true) {
		return;
	}

	$timestamp = gmdate('Y-m-d H:i:s');
	$message = sprintf('[%s] [kinsta-mu-plugins.DEBUG] %s', $timestamp, $message);

	if ($context !== []) {
		$message .= ' ' . json_encode($context);
	}

	file_put_contents(WP_CONTENT_DIR . '/kinsta-mu-plugins.log', $message . "\n", FILE_APPEND);
}
