<?php
// Super early WordPress cache loading
if (!defined('WP_CACHE')) {
    define('WP_CACHE', true);
}

$cache_dir = __DIR__ . '/plugins/wp-super-fast-cache-plugin/cache/';
$cache_file = $cache_dir . md5($_SERVER['REQUEST_URI']) . '.html';

if (file_exists($cache_file) && filesize($cache_file) > 0) {
    header("Content-Type: text/html; charset=UTF-8");
    header("Cache-Control: public, max-age=3600");
    
    if (ob_get_level()) ob_end_clean();
    echo file_get_contents($cache_file);
    exit;
}