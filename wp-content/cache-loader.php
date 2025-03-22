<?php
// Super early WordPress cache loading
define('MY_CACHE_DIR2', __DIR__ . '/plugins/wp-super-fast-cache-plugin/cache/');

$request_uri = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);

$cache_file = MY_CACHE_DIR2 . $request_uri . '.html';

if (file_exists($cache_file) && filesize($cache_file) > 0) {
    header("Content-Type: text/html; charset=UTF-8");
    header("Cache-Control: public, max-age=86400"); // 24 hours

    if (ob_get_level()) ob_end_clean();
    readfile($cache_file);
    exit;
}
