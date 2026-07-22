<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$pathFile = __DIR__.'/kaevcms-path.php';
if (! is_file($pathFile)) {
    http_response_code(500);
    echo 'KaevCMS core path configuration is missing.';
    exit;
}

$projectRoot = require $pathFile;
if (! is_string($projectRoot) || ! is_file($projectRoot.'/bootstrap/app.php')) {
    http_response_code(500);
    echo 'KaevCMS core directory could not be found.';
    exit;
}

if (! is_file($projectRoot.'/.env')) {
    $scriptDirectory = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php')));
    $basePath = $scriptDirectory === '/' ? '' : rtrim($scriptDirectory, '/');
    header('Location: '.$basePath.'/install/', true, 302);
    exit;
}

if (file_exists($maintenance = $projectRoot.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $projectRoot.'/vendor/autoload.php';
$application = require $projectRoot.'/bootstrap/app.php';
$application->usePublicPath(__DIR__);
$application->handleRequest(Request::capture());
