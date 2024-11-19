<?php

namespace PixlMint\CMS\Controllers;

use Nacho\Contracts\UserHandlerInterface;
use Nacho\Helpers\HookHandler;
use Nacho\Models\HttpResponse;
use Nacho\ORM\ModelInterface;
use PixlMint\CMS\Anchors\InitAnchor;
use PixlMint\CMS\Helpers\CMSConfiguration;
use PixlMint\CMS\Helpers\TokenHelper;
use Nacho\Controllers\AbstractController;
use PixlMint\CMS\Models\TokenUser;

class InitController extends AbstractController
{
    const NO_TOKEN_SET = 'no_token_set';
    const TOKEN_VALID = 'token_valid';
    const TOKEN_INVALID = 'token_invalid';
    private HookHandler $hookHandler;
    private UserHandlerInterface $userHandler;
    private CMSConfiguration $cmsConfiguration;
    private TokenHelper $tokenHelper;

    public function __construct(HookHandler $hookHandler, UserHandlerInterface $userHandler, CMSConfiguration $cmsConfiguration, TokenHelper $tokenHelper)
    {
        parent::__construct();
        $this->hookHandler = $hookHandler;
        $this->userHandler = $userHandler;
        $this->cmsConfiguration = $cmsConfiguration;
        $this->tokenHelper = $tokenHelper;
    }

    public function init(): HttpResponse
    {
        $isTokenValid = $this->isTokenValid();
        $isAdminCreated = $this->isAdminCreated();
        $version = $this->cmsConfiguration->version();

        $init = ['is_token_valid' => $isTokenValid, 'version' => $version, 'adminCreated' => $isAdminCreated];
        $init = $this->hookHandler->executeHook(InitAnchor::getName(), ['init' => $init]);

        return $this->json($init);
    }

    public function isAdminCreated(): bool
    {
        $users = $this->userHandler->getUsers();
        foreach ($users as $user) {
            if ($user instanceof ModelInterface) {
                $user = $user->toArray();
            }
            if ($user['role'] === 'Editor') {
                return true;
            }
        }

        return false;
    }

    private function isTokenValid(): string
    {
        if (!TokenHelper::getPossibleTokenFromRequest()) {
            return self::NO_TOKEN_SET;
        }

        $users = $this->userHandler->getUsers();

        if ($this->tokenHelper->isTokenValid(TokenHelper::getPossibleTokenFromRequest(), $users)) {
            return self::TOKEN_VALID;
        }

        return self::TOKEN_INVALID;
    }
}