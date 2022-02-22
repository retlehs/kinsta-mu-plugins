# Kinsta Must Use (MU) Plugin

A simple mirror of the official Kinsta Mu Plugin with added Composer/[Bedrock](https://github.com/roots/bedrock) support.

For latest version/info please see the official [Kinsta MU Plugin page](https://kinsta.com/help/kinsta-mu-plugin/).

## Installing with Composer (for Bedrock)

1. Add this plugin repo to `repositories`in your Bedrock `composer.json` file:
    ```diff
      "repositories": {
        "wpackagist": {
          "type": "composer",
          "url": "https://wpackagist.org",
          "only": [
            "wpackagist-plugin/*",
            "wpackagist-theme/*"
          ]
        },
    +   "kinsta-mu-plugins": {
    +     "type": "vcs",
    +     "url": "git@github.com:retlehs/kinsta-mu-plugins"
    +   },
    ```
1. Add the plugin requirement with specific version number (or `*` for latest) to `composer.json`:
    ```diff
      "require": {
        "php": ">=7.4",
        "composer/installers": "^2.0",
        ...
    +   "kinsta/kinsta-mu-plugins": "*",
        ...
      }
    ```
1. Run `composer update` from the Bedrock directory.


## White label

Enabling white labeling will change the following elements in the WordPress dashboard:

1. The branded **Kinsta Cache** sidebar link will be changed to an unbranded **Cache Settings** link.
1. The **Thanks for creating with WordPress and hosting with Kinsta** message near the bottom of the dashboard will be replaced with **Thank you for creating with WordPress**.
1. The Kinsta logo on the **Cache Control** page will be removed or replaced with an image of your choice.
1. The links to Kinsta documentation and support will be removed.

```php
define('KINSTAMU_WHITELABEL', true);
define('KINSTAMU_LOGO', 'https://mylogo.com/mylogo.jpg');
```

For more info, refer to the [official docs here](https://kinsta.com/help/white-label-kinsta-wordpress-admin/)


## Bedrock + Kinsta
The following constants may be required to fix issues with CDN paths + shared plugin asset URLs.

```php
/**
 * Kinsta CDN fix for Bedrock
 */
define('KINSTA_CDN_USERDIRS', 'app');

/**
 * Fix Kinsta MU Plugins URL path with Bedrock
 */
$mu_plugins_url = Config::get('WP_CONTENT_URL') . '/mu-plugins';
define('KINSTAMU_CUSTOM_MUPLUGIN_URL', "{$mu_plugins_url}/kinsta-mu-plugins");
```
