<?php

use PixlMint\CMS\Controllers\AuthenticationController;
use PixlMint\CMS\Controllers\AdminController;
use PixlMint\CMS\Controllers\InitController;
use PixlMint\CMS\Controllers\UsersController;
use PixlMint\CMS\Controllers\ViewPageController;

return [
    [
        "route" => "/api/entry/view",
        "controller" => ViewPageController::class,
        "function" => "loadEntry",
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
        "route" => "/api/admin/entry/delete",
        "controller" => AdminController::class,
        "function" => "delete"
    ],
    [
        // TODO Postman
        "route" => "/api/admin/users/list",
        "min_role" => "Editor",
        "controller" => UsersController::class,
        "function" => "list"
    ],
    [
        // TODO Postman
        "route" => "/api/admin/users/add",
        "min_role" => "Editor",
        "controller" => UsersController::class,
        "function" => "add"
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