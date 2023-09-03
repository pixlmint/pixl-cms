<?php

use Nacho\Helpers\AlternativeContentHandlers\PDFContentType;

return [
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
    ],
];