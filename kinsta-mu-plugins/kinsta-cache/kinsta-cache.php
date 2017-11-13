<?php namespace Kinsta;

define( 'KINSTA_CACHE_DOCS_URL', 'https://kinsta.com/knowledgebase/kinsta-cache-plugin/' );

include( 'KinstaCachePurge.php' );
include( 'KinstaCacheAdmin.php' );
include( 'KinstaCache.php' );

$config = array(
    'option_name' => 'kinsta-cache-settings',
    'immediate_path' => 'https://localhost/kinsta-clear-cache/v2/immediate',
    'throttled_path' => 'https://localhost/kinsta-clear-cache/v2/throttled',
);

$default_settings = array(
    'version' => '2.0',
    'options' => array(
        'additional_paths' => array(
            'group' => array(),
            'single' => array()
        ),
    ),
);

$KinstaCache = new KinstaCache( $config, $default_settings );
