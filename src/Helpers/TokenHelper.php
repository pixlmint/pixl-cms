<?php

namespace PixlMint\CMS\Helpers;

use Nacho\Security\UserRepository;
use PixlMint\CMS\Exception\InvalidTokenException;
use PixlMint\CMS\Models\TokenUser;
use Nacho\ORM\ModelInterface;
use Nacho\ORM\TemporaryModel;
use Nacho\Security\UserInterface;

class TokenHelper
{
    private SecretHelper $secretHelper;
    private UserRepository $userRepository;

    public function __construct(SecretHelper $secretHelper, UserRepository $userRepository)
    {
        $this->secretHelper = $secretHelper;
        $this->userRepository = $userRepository;
    }

    public function getToken($user): string
    {
        if ($user instanceof TokenUser) {
            $user = $user->toArray();
        }
        $secret = $this->secretHelper->getSecret();
        $tokenStamp = $user['tokenStamp'];

        return md5($tokenStamp . $secret);
    }

    /**
     * Any token that isn't null, false, undefined, or other impossible tokens
     */
    public static function getPossibleTokenFromRequest(): string|null
    {
        $unsafeToken = self::getTokenFromRequest();
        if (self::isTokenPossible($unsafeToken)) {
            return null;
        }

        return $unsafeToken;
    }

    public static function getTokenFromRequest(): string|null
    {
        $key = 'HTTP_PIXLTOKEN';
        if (key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        } elseif (key_exists('PIXLTOKEN', $_COOKIE)) {
            return $_COOKIE['PIXLTOKEN'];
        }

        return null;
    }

    public function isTokenValid($token, $users): bool
    {
        try {
            $this->getUserByToken($token, $users);
            return true;
        } catch (InvalidTokenException $e) {
        }
        return false;
    }

    /**
     * @param string $token
     * @param array $users
     * @return UserInterface|ModelInterface
     * @throws InvalidTokenException
     */
    public function getUserByToken(string $token, array $users): UserInterface|ModelInterface
    {
        foreach ($users as $id => $user) {
            if ($token === $this->getToken($user)) {
                if (is_array($user)) {
                    $user = TokenUser::init(new TemporaryModel($user), $id);
                    $this->userRepository->setInitialized($id, $user);
                    return $user;
                } else {
                    return $user;
                }
            }
        }

        throw new InvalidTokenException("No user for the provided token found");
    }

    public function generateNewTokenStamp(TokenUser &$user): void
    {
        $strTokenStamp = random_bytes(100) . time();

        $user->setTokenStamp(sha1($strTokenStamp));
    }

    private static function isTokenPossible(?string $token): bool
    {
        if ($token) {
            $token = strtolower($token);
        } else {
            return false;
        }
        return $token === 'null' || $token === 'undefined' || $token === 'false';
    }
}
