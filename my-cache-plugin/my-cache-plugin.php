<?php
/*
Plugin Name: My Super Fast Cache Plugin
Description: A simple page caching plugin that loads cache before WordPress initializes.
Version: 1.0
Author: Your Name
*/

// Define paths
define('MY_CACHE_DIR', __DIR__ . '/cache/');
define('MY_CACHE_LOG', MY_CACHE_DIR . 'cache-log.txt');
define('ADVANCED_CACHE_PATH', WP_CONTENT_DIR . '/advanced-cache.php');

// Ensure cache directory exists
if (!file_exists(MY_CACHE_DIR)) {
    mkdir(MY_CACHE_DIR, 0755, true);
}

// ✅ Enable `advanced-cache.php` when the plugin is activated
function my_cache_enable_advanced_cache() {
    $advanced_cache_code = <<<PHP
<?php
// Super early WordPress cache loading
if (!defined('WP_CACHE')) {
    define('WP_CACHE', true);
}

\$cache_dir = __DIR__ . '/plugins/my-cache-plugin/cache/';
\$cache_file = \$cache_dir . md5(\$_SERVER['REQUEST_URI']) . '.html';

if (file_exists(\$cache_file) && filesize(\$cache_file) > 0) {
    header("Content-Type: text/html; charset=UTF-8");
    header("Cache-Control: public, max-age=3600");
    
    if (ob_get_level()) ob_end_clean();
    echo file_get_contents(\$cache_file);
    exit;
}
PHP;

    file_put_contents(ADVANCED_CACHE_PATH, $advanced_cache_code);
    update_option('wp_cache', true); // Enable caching in WordPress
}

register_activation_hook(__FILE__, 'my_cache_enable_advanced_cache');

// ✅ Remove `advanced-cache.php` when the plugin is deactivated
function my_cache_disable_advanced_cache() {
    if (file_exists(ADVANCED_CACHE_PATH)) {
        unlink(ADVANCED_CACHE_PATH);
    }
    delete_option('wp_cache');
}
register_deactivation_hook(__FILE__, 'my_cache_disable_advanced_cache');

// ✅ Start output buffering to save the cache
function my_cache_start() {
    if (is_user_logged_in() || is_admin() || $_SERVER['REQUEST_METHOD'] !== 'GET') {
        return;
    }

    $cache_file = MY_CACHE_DIR . md5($_SERVER['REQUEST_URI']) . '.html';

    if (file_exists($cache_file) && filesize($cache_file) > 0) {
        file_put_contents(MY_CACHE_LOG, "✅ Serving cached file: $cache_file" . PHP_EOL, FILE_APPEND);

        if (ob_get_level()) {
            ob_end_clean();
        }

        header("Content-Type: text/html; charset=UTF-8");
        header("Cache-Control: public, max-age=3600");

        echo file_get_contents($cache_file);
        exit;
    }

    ob_start();
}
add_action('init', 'my_cache_start', 1);

// ✅ Save cache after page loads
function my_cache_end() {
    if (is_user_logged_in() || is_admin() || $_SERVER['REQUEST_METHOD'] !== 'GET') {
        return;
    }

    $cache_file = MY_CACHE_DIR . md5($_SERVER['REQUEST_URI']) . '.html';
    $output = ob_get_contents();

    if (!empty($output)) {
        file_put_contents($cache_file, $output);
        file_put_contents(MY_CACHE_LOG, "✅ Cache file saved: $cache_file" . PHP_EOL, FILE_APPEND);
    } else {
        file_put_contents(MY_CACHE_LOG, "❌ ERROR: Output buffer was empty when saving cache!" . PHP_EOL, FILE_APPEND);
    }

    ob_end_flush();
}
add_action('wp_footer', 'my_cache_end', 100);

// ✅ Clear cache when a post is updated
function my_cache_clear() {
    $cache_files = glob(MY_CACHE_DIR . '*.html');
    foreach ($cache_files as $file) {
        unlink($file);
    }
    file_put_contents(MY_CACHE_LOG, "🗑️ Cache cleared!" . PHP_EOL, FILE_APPEND);
}
add_action('save_post', 'my_cache_clear');

// ✅ Add "Clear Cache" button in the admin bar
function my_cache_admin_bar($wp_admin_bar) {
    $wp_admin_bar->add_menu([
        'id'    => 'clear_my_cache',
        'title' => 'Clear Cache',
        'href'  => admin_url('?clear_my_cache=1'),
    ]);
}
add_action('admin_bar_menu', 'my_cache_admin_bar', 100);

// ✅ Admin notice for cache clearing
function my_cache_admin_notice() {
    if (isset($_GET['clear_my_cache'])) {
        my_cache_clear();
        echo '<div class="updated"><p>Cache cleared successfully!</p></div>';
    }
}
add_action('admin_notices', 'my_cache_admin_notice');

//working