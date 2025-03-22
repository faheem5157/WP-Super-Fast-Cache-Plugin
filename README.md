# WP-Super-Fast-Cache-Plugin
## Configuration

Place the files advanced-cache.php, cache-loader.php in wp-content folder

Add the following line of code at top of wp-config.php

define( 'WP_CACHE', true );
if (file_exists(__DIR__ . '/wp-content/cache-loader.php')) {
    require_once __DIR__ . '/wp-content/cache-loader.php';
}