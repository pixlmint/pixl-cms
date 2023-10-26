<?php

namespace PixlMint\CMS\Helpers;

use Nacho\ORM\RepositoryInterface;
use Nacho\ORM\RepositoryManager;
use PixlMint\CMS\Models\Secret;
use PixlMint\CMS\Repository\SecretRepository;

class SecretHelper
{
    private static ?string $secret = null;
    private static RepositoryInterface $secretRepository;

    public static function getSecret(): string
    {
        if (!self::$secret) {
            self::readSecret();
        }

        return self::$secret;
    }

    public static function setSecret(string $secret): void
    {
        self::initRepo();
        $secretObj = new Secret(1, $secret);
        self::$secretRepository->set($secretObj);
        self::$secret = $secret;
    }

    private static function readSecret(): void
    {
        self::initRepo();
        /** @var Secret $secretObj */
        $secretObj = self::$secretRepository->getById(1);
        if (!$secretObj) {
            self::generateNewSecret();
            return;
        }

        self::$secret = $secretObj->getSecret();
    }

    protected static function generateNewSecret(): void
    {
        static::setSecret(md5(bin2hex(random_bytes(256))));
    }

    private static function initRepo(): void
    {
        if (!isset(self::$secretRepository)) {
            self::$secretRepository = RepositoryManager::getInstance()->getRepository(SecretRepository::class);
        }
    }
}