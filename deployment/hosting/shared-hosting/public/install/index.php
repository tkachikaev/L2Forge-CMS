<?php

declare(strict_types=1);

$publicRoot = dirname(__DIR__);
$pathFile = $publicRoot.'/kaevcms-path.php';
if (! is_file($pathFile)) {
    http_response_code(500);
    echo 'KaevCMS core path configuration is missing.';
    exit;
}

$projectRoot = require $pathFile;
$installer = is_string($projectRoot)
    ? $projectRoot.'/deployment/hosting/web-installer/installer.php'
    : '';

define('KAEVCMS_INSTALL_ENTRY', true);
define('KAEVCMS_SHARED_HOSTING', true);
define('KAEVCMS_PROJECT_ROOT', is_string($projectRoot) ? $projectRoot : '');
define('KAEVCMS_PUBLIC_PATH', $publicRoot);

if (! is_file($installer)) {
    http_response_code(500);
    echo 'KaevCMS web installer is missing.';
    exit;
}

require $installer;
