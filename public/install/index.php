<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__, 2);
$installer = $projectRoot.'/deployment/hosting/web-installer/installer.php';

define('KAEVCMS_INSTALL_ENTRY', true);
define('KAEVCMS_PROJECT_ROOT', $projectRoot);
define('KAEVCMS_PUBLIC_PATH', dirname(__DIR__));

if (! is_file($installer)) {
    http_response_code(500);
    echo 'KaevCMS web installer is missing.';
    exit;
}

require $installer;
