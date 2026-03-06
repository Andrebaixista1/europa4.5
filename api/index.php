<?php

declare(strict_types=1);

if (getenv('VERCEL') !== false) {
    $tmpStoragePath = '/tmp/storage';
    $viewCompiledPath = $tmpStoragePath.'/framework/views';
    $runtimeDirs = [
        $tmpStoragePath.'/framework/cache/data',
        $tmpStoragePath.'/framework/sessions',
        $tmpStoragePath.'/framework/views',
        $tmpStoragePath.'/framework/testing',
        $tmpStoragePath.'/logs',
    ];

    foreach ($runtimeDirs as $dir) {
        if (! is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
    }

    putenv('APP_STORAGE='.$tmpStoragePath);
    $_ENV['APP_STORAGE'] = $tmpStoragePath;
    $_SERVER['APP_STORAGE'] = $tmpStoragePath;

    putenv('VIEW_COMPILED_PATH='.$viewCompiledPath);
    $_ENV['VIEW_COMPILED_PATH'] = $viewCompiledPath;
    $_SERVER['VIEW_COMPILED_PATH'] = $viewCompiledPath;
}

$publicPath = __DIR__.'/../public';

$_SERVER['SCRIPT_FILENAME'] = $publicPath.'/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['DOCUMENT_ROOT'] = $publicPath;

chdir($publicPath);

require $publicPath.'/index.php';
