<?php
$config = require __DIR__ . '/../config/config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name($config['session_name']);
    session_start();
}

spl_autoload_register(function($class) {
    $base = __DIR__;
    $file = $base . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) require $file;
});

require __DIR__ . '/helpers.php';

if (!is_dir($config['data_dir'])) mkdir($config['data_dir'], 0775, true);
if (!is_dir($config['logs_dir'])) mkdir($config['logs_dir'], 0775, true);

DB::init($config['db_path']);
DB::migrate();
