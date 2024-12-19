<?php

use PixlMint\CMS\Controllers\AdminController;
use PixlMint\CMS\Controllers\AlternativeContentController;
use PixlMint\CMS\Controllers\AuthenticationController;
use PixlMint\CMS\Controllers\InitController;
use PixlMint\CMS\Controllers\NotFoundController;
use PixlMint\CMS\Controllers\ViewPageController;
use PixlMint\CMS\Controllers\NavController;

return [
    [
        "route" => '/',
        "controller" => NotFoundController::class,
        "function" => "index",
    ],
    [
        'route' => '/api/nav',
        'controller' => NavController::class,
        'function' => 'loadNav',
    ],
    [
        "route" => "/api/entry/view",
        "controller" => ViewPageController::class,
        "function" => "loadEntry",
    ],
    [
        "route" => "/api/entry/load-pdf",
        "controller" => AlternativeContentController::class,
        "function" => "loadPdf",
    ],
    [
        "route" => "/api/admin/entry/rename",
        "function" => "rename",
        "controller" => AdminController::class,
    ],
    [
        "route" => "/api/admin/entry/add",
        "controller" => AdminController::class,
        "function" => "add"
    ],
    [
        "route" => "/api/admin/folder/add",
        "controller" => AdminController::class,
        "function" => "addFolder"
    ],
    [
        "route" => "/api/admin/folder/delete",
        "controller" => AdminController::class,
        "function" => "deleteFolder"
    ],
    [
        "route" => "/api/admin/entry/edit",
        "controller" => AdminController::class,
        "function" => "edit"
    ],
    [
        "route" => "/api/admin/entry/move",
        "controller" => AdminController::class,
        "function" => "moveEntry"
    ],
    [
        'route' => '/api/admin/generate-backup',
        'controller' => AdminController::class,
        'function' => 'generateBackup',
    ],
    [
        'route' => '/api/admin/restore-backup',
        'controller' => AdminController::class,
        'function' => 'restoreFromBackup',
    ],
    [
        "route" => "/api/admin/entry/delete",
        "controller" => AdminController::class,
        "function" => "delete"
    ],
    [
        "route" => "/api/admin/entry/update-alternative-content",
        "controller" => AlternativeContentController::class,
        "function" => "update",
    ],
    [
        "route" => "/api/admin/entry/upload-alternative-content",
        "controller" => AlternativeContentController::class,
        "function" => "upload",
    ],
    [
        "route" => "/api/admin/entry/change-security",
        "controller" => AdminController::class,
        "function" => "changePageSecurity",
    ],
    [
        "route" => "/api/admin/entry/fetch-last-changed",
        "controller" => AdminController::class,
        "function" => "fetchLastChanged",
    ],
    [
        "route" => "/api/admin/entry/view-markdown",
        "controller" => AdminController::class,
        "function" => "loadMarkdownFile",
    ],
    [
        "route" => "/api/auth/login",
        "controller" => AuthenticationController::class,
        "function" => "login"
    ],
    [
        "route" => "/api/auth/change-password",
        "controller" => AuthenticationController::class,
        "function" => "changePassword",
    ],
    [
        "route" => "/api/auth/request-new-password",
        "controller" => AuthenticationController::class,
        "function" => "requestNewPassword",
    ],
    [
        "route" => "/api/auth/restore-password",
        "controller" => AuthenticationController::class,
        "function" => "restorePassword",
    ],
    [
        "route" => "/api/auth/generate-new-token",
        "controller" => AuthenticationController::class,
        "function" => "generateNewToken",
    ],
    [
        "route" => "/api/auth/destroy-secret",
        "controller" => AuthenticationController::class,
        "function" => "destroySecret",
    ],
    [
        "route" => "/api/auth/destroy-token",
        "controller" => AuthenticationController::class,
        "function" => "destroyToken",
    ],
    [
        "route" => "/api/auth/create-admin",
        "controller" => AuthenticationController::class,
        "function" => 'createAdmin',
    ],
    [
        "route" => "/api/init",
        "controller" => InitController::class,
        "function" => "init",
    ],
];
