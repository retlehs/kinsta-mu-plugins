# Kinsta Must Use (MU) Plugin

A simple mirror of the official Kinsta Mu Plugin with added Composer/[Bedrock](https://github.com/roots/bedrock) support.

> [!NOTE]
> Kinsta now includes [Composer installation instructions]([https://kinsta.com/help/kinsta-mu-plugin/#installing-via-composer](https://kinsta.com/docs/wordpress-hosting/kinsta-mu-plugin/?kaid=OFDHAJIXUDIV#installing-via-composer)) in their official docs. However, this package is still useful as it supports versioning, allowing you to pin specific releases in your `composer.json`.

For latest version/info please see the official [Kinsta MU Plugin page](https://kinsta.com/docs/wordpress-hosting/kinsta-mu-plugin/?kaid=OFDHAJIXUDIV#installing-via-composer).

## Installing with Composer (for Bedrock)

1. Add this plugin repo from the Bedrock directory:
    ```sh
    composer config repositories.kinsta-mu-plugins vcs git@github.com:retlehs/kinsta-mu-plugins
    ```
1. Require the plugin with a specific version (or `*` for latest):
    ```sh
    composer require kinsta/kinsta-mu-plugins
    ```


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

For more info, refer to the [official docs here](https://kinsta.com/docs/wordpress-hosting/kinsta-mu-plugin/?kaid=OFDHAJIXUDIV#white-label-and-customize-the-kinsta-mu-plugin)


## Bedrock + Kinsta
The following constants may be required to fix issues with CDN paths + shared plugin asset URLs.

```php
/**
 * Kinsta CDN fix for Bedrock
 */
Config::define('KINSTA_CDN_USERDIRS', 'app');

/**
 * Fix Kinsta MU Plugins URL path with Bedrock
 */
$mu_plugins_url = Config::get('WP_CONTENT_URL') . '/mu-plugins';
Config::define('KINSTAMU_CUSTOM_MUPLUGIN_URL', "{$mu_plugins_url}/kinsta-mu-plugins");
```

## Changelog

[https://kinsta.com/changelog/mu-plugin-changelog/](https://kinsta.com/changelog/mu-plugin-changelog/?kaid=OFDHAJIXUDIV)
