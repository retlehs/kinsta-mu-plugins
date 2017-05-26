<?php
namespace Kinsta;

$config = array(
    'option_name' => 'kinsta-cache-settings',
    'purge_urls' => array(
        'all' => site_url() . '/kinsta-clear-cache-all',
        'path' => site_url() . '/kinsta-clear-cache',
        'mobilepath' => site_url() . '/kinsta-clear-mobile-cache'
    )
);


$default_settings = array(
    'version' => '1.41',
    'options' => array(
        'purge_static_home' => false,
        'page_depth_blog' => 5,
        'page_depth_archives' => 2,
        'purge_blog_feeds' => true,
        'purge_archive_feeds' => false,
        'purge_date_archives' => false,
        'has_mobile_plugin' => false
    ),
    'rules' => array(
        'blog' => array(
            'post_added' => true,
            'post_modified' => true,
            'post_unpublished' => true,
        ),
        'post' => array(
            'post_added' => true,
            'post_modified' => true,
            'post_unpublished' => true,
            'comment_added' => true,
            'comment_modified' => true,
            'comment_unpublished' => true,
        ),
        'archive' => array(
            'post_added' => true,
            'post_modified' => true,
            'post_unpublished' => true,
        )
    )
);

include( 'KinstaCachePurge.php' );
include( 'KinstaCacheAdmin.php' );
include( 'KinstaCache.php' );

new KinstaCache( $config, $default_settings );
