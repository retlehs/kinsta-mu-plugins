<?php
/**
 * Bootstrap classes and functions for the Kinsta MU Plugin.
 */

use Kinsta\KMP;

if (! defined('ABSPATH')) { // If this file is called directly.
	die('No script kiddies please!');
}

global $kinsta_muplugin;
global $kinsta_cache;
global $KinstaCache; // phpcs:ignore

try {
	$kinsta_muplugin = new KMP();
	$kinsta_cache = $kinsta_muplugin;
	$KinstaCache = $kinsta_muplugin; // phpcs:ignore
} catch ( \Throwable $throwable ) {
	error_log(
		sprintf(
			'[kinsta-mu-plugins.ERROR]: %s in %s:%d',
			$throwable->getMessage(),
			$throwable->getFile(),
			$throwable->getLine()
		)
	);

	$kinsta_muplugin = null;
	$kinsta_cache = null;
	$KinstaCache = null; // phpcs:ignore
}
