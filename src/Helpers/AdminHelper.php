<?php

namespace PixlMint\CMS\Helpers;

use Nacho\Security\JsonUserHandler;

class AdminHelper
{
    public static function isAdminCreated(): bool
    {
        $userHandler = new JsonUserHandler();
        $users = $userHandler->getUsers();
        foreach ($users as $user) {
            if ($user['role'] === 'Editor') {
                return true;
            }
        }

        return false;
    }
}