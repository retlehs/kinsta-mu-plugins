<?php

namespace Kinsta\KMP;

/**
 * Check whether the global autopurge is enabled.
 */
function is_autopurge_enabled(): bool
{
    if (defined( 'KINSTAMU_DISABLE_AUTOPURGE' ) && KINSTAMU_DISABLE_AUTOPURGE === true) {
        return false;
    }

    $status = get_option('kinsta-autopurge-status', null);

    return $status === 'enabled' || $status === null;
}

/**
 * Log debug messages in the PHP error log if KINSTAMU_DEBUG_LOG is enabled.
 *
 * @param string $message
 * @param array $context
 * @return void
 */
function debug_log(string $message, array $context = []): void
{
    /**
     * Note: KINSTAMU_DEBUG_LOG is currently experimental and may be renamed in the future.
     */
    if (defined('KINSTAMU_DEBUG_LOG') && KINSTAMU_DEBUG_LOG === true) {
        $message = "[kinsta-mu-plugins.DEBUG] " . $message;

        if ($context !== []) {
            $message .= ' ' . json_encode($context);
        }

        error_log($message . "\n");
    }
}
