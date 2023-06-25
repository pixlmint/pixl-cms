<?php

namespace PixlMint\CMS\Controllers;

use Nacho\Helpers\HookHandler;
use Nacho\Nacho;
use PixlMint\CMS\Anchors\InitAnchor;
use PixlMint\CMS\Helpers\CMSConfiguration;
use PixlMint\CMS\Helpers\TokenHelper;
use Nacho\Controllers\AbstractController;

class InitController extends AbstractController
{
    const NO_TOKEN_SET = 'no_token_set';
    const TOKEN_VALID = 'token_valid';
    const TOKEN_INVALID = 'token_invalid';

    public function init(): string
    {
        $isTokenValid = $this->isTokenValid();
        $isAdminCreated = $this->isAdminCreated();
        $version = CMSConfiguration::version();

        $init = ['is_token_valid' => $isTokenValid, 'version' => $version, 'adminCreated' => $isAdminCreated];
        $init = HookHandler::getInstance()->executeHook(InitAnchor::getName(), ['init' => $init]);

        return $this->json($init);
    }

    public function isAdminCreated(): bool
    {
        $users = $this->nacho->getUserHandler()->getUsers();
        foreach ($users as $user) {
            if ($user['role'] === 'Editor') {
                return true;
            }
        }

        return false;
    }

    private function isTokenValid(): string
    {
        if (!TokenHelper::getTokenFromRequest() || $this->isTokenNull()) {
            return self::NO_TOKEN_SET;
        }

        $tokenHelper = new TokenHelper();

        $users = $this->nacho->userHandler->getUsers();

        if ($tokenHelper->isTokenValid(TokenHelper::getTokenFromRequest(), $users)) {
            return self::TOKEN_VALID;
        }

        return self::TOKEN_INVALID;
    }

    private function isTokenNull(): bool
    {
        $token = TokenHelper::getTokenFromRequest();

        return $token === null || $token === 'null' || $token === 'undefined';
    }
}