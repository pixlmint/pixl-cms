<?php


if (getenv('CLI_VENDOR_DIR') !== false) {
    include getenv('CLI_VENDOR_DIR') . '/autoload.php';
} else {
    include $_composer_autoload_path ?? __DIR__ . '/../vendor/autoload.php';
}

use PixlMint\CMS\CmsCore;

$_SERVER['DOCUMENT_ROOT'] = '/var/www/html';

$cmsCore = new CmsCore();

$cli = $cmsCore->cliInit();

$cli->run($argv);

