<?php

namespace PixlMint\CMS\Helpers;

use Nacho\Nacho;
use Nacho\ORM\ModelInterface;
use PixlMint\CMS\Exception\InvalidTokenException;
use PixlMint\CMS\Models\TokenUser;
use Nacho\Security\JsonUserHandler;
use Nacho\Contracts\UserHandlerInterface;
use Nacho\Security\UserInterface;

final class CustomUserHelper extends JsonUserHandler implements UserHandlerInterface
{
    const ROLE_SUPER_ADMIN = 'Super Admin';
    const ROLE_EDITOR = 'Editor';
    const ROLE_READER = 'Reader';
    const ROLE_GUEST = 'Guest';
    private SecretHelper $secretHelper;
    private TokenHelper $tokenHelper;

    public function __construct()
    {
        parent::__construct();
        $this->secretHelper = Nacho::$container->get(SecretHelper::class);
        $this->tokenHelper = Nacho::$container->get(TokenHelper::class);
    }

    public function getCurrentUser(): ModelInterface|UserInterface|null
    {
        if (!key_exists('HTTP_PIXLTOKEN', $_SERVER)) {
            return new TokenUser(0, 'Guest', self::ROLE_GUEST, null, null, null, null, null, null);
        }

        $token = TokenHelper::getPossibleTokenFromRequest();

        try {
            return $this->tokenHelper->getUserByToken($token, $this->getUsers());
        } catch (InvalidTokenException $e) {
            return null;
        }
    }

    public function isGranted(string $minRight = self::ROLE_GUEST, ?UserInterface $user = null): bool
    {
        if (!$user) {
            $user = $this->getCurrentUser();
        }
        if (!$user) {
            return $minRight === self::ROLE_GUEST;
        }
        return parent::isGranted($minRight, $user);
    }

    public function setPassword(string $username, string $newPassword): UserInterface
    {
        /** @var TokenUser $user */
        $user = $this->findUser($username);
        $this->setPasswordForUser($user, $newPassword);

        return $user;
    }

    public function setPasswordForUser(TokenUser $user, string $newPassword): TokenUser
    {
        $passwordHash = password_hash($this->secretHelper->getSecret() . $newPassword, PASSWORD_DEFAULT);
        $user->setPassword($passwordHash);
        $this->userRepository->set($user);

        return $user;
    }

    public function passwordVerify(UserInterface $user, string $password): bool
    {
        $secret = $this->secretHelper->getSecret();

        return password_verify($secret . $password, $user->getPassword());
    }
}
