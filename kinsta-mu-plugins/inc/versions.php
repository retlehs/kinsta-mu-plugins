<?php

namespace Kinsta\KMP;

/**
 * Define the plugin version.
 *
 * Users can use this constant to check the plugin version, for example:
 *
 * @example
 * if ( version_compare( Kinsta\KMP\VERSION, '3.6.0', '>=' ) ) {}
 */
const VERSION = '3.6.0';

/**
 * Maintain backward compatibility by defining the `KINSTAMU_VERSION` constant.
 *
 * @deprecated 3.6.0 Use `Kinsta\KMP\VERSION`.
 */
define('KINSTAMU_VERSION', VERSION);
