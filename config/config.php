<?php

use Nacho\Helpers\AlternativeContentHandlers\JupyterNotebookContentType;
use Nacho\Helpers\AlternativeContentHandlers\PDFContentType;
use Nacho\Helpers\ConfigMerger;

$userConfiguration  = [];
if (is_file('/config/config.php')) {
    $userConfiguration = include_once("/config/config.php");
}

$cmsConfig = [
    'routes' => require_once('routes.php'),
    'hooks' => [
        [
            'anchor' => 'post_find_route',
            'hook' => PixlMint\CMS\Hooks\RouteCheckHook::class,
        ],
    ],
    'base' => require_once('base.php'),
    'security' => [
        'user_model' => PixlMint\CMS\Models\TokenUser::class,
        'userHandler' => PixlMint\CMS\Helpers\CustomUserHelper::class,
    ],
    'alternativeContentHandlers' => [
        PDFContentType::class,
        JupyterNotebookContentType::class,
    ],
    'commands' => require_once("commands.php"),
];

if ($userConfiguration) {
    return ConfigMerger::merge([$cmsConfig, $userConfiguration]);
} else {
    return $cmsConfig;
}
