<?php

namespace PixlMint\CMS\Helpers;

use Nacho\Contracts\UserHandlerInterface;
use Nacho\Nacho;

class AdminHelper
{
    public static function isAdminCreated(): bool
    {
        $users = Nacho::$container->get(UserHandlerInterface::class)->getUsers();
        foreach ($users as $user) {
            if ($user['role'] === 'Editor') {
                return true;
            }
        }

        return false;
    }
}