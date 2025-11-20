<?php

// Configure writable directories for Vercel serverless environment
$_ENV['APP_STORAGE'] = '/tmp/storage';
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';

// Create necessary directories in /tmp
$directories = [
    '/tmp/storage',
    '/tmp/storage/framework',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
    '/tmp/bootstrap',
    '/tmp/bootstrap/cache',
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Create empty cache files if they don't exist
$cacheFiles = [
    '/tmp/bootstrap/cache/services.php' => '<?php return [];',
    '/tmp/bootstrap/cache/packages.php' => '<?php return [];',
];

foreach ($cacheFiles as $file => $content) {
    if (!file_exists($file)) {
        file_put_contents($file, $content);
    }
}

// Override bootstrap cache path
define('LARAVEL_BOOTSTRAP_CACHE', '/tmp/bootstrap/cache');

// Set log path to /tmp
if (!defined('STDERR')) {
    define('STDERR', fopen('/tmp/storage/logs/laravel.log', 'a'));
}

require __DIR__ . '/../public/index.php';
