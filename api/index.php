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
    '/tmp/bootstrap/cache',
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Set log path to /tmp
if (!defined('STDERR')) {
    define('STDERR', fopen('/tmp/storage/logs/laravel.log', 'a'));
}

require __DIR__ . '/../public/index.php';
