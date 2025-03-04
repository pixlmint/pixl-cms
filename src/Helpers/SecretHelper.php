<?php

namespace PixlMint\CMS\Helpers;

use Nacho\Nacho;
use Nacho\ORM\RepositoryInterface;
use PixlMint\CMS\Models\Secret;
use PixlMint\CMS\Repository\SecretRepository;

class SecretHelper
{
    private ?string $secret = null;
    private RepositoryInterface $secretRepository;

    public function __construct()
    {
        $this->secretRepository = Nacho::$container->get(SecretRepository::class);
    }

    public function getSecret(): string
    {
        if (!$this->secret) {
            $this->readSecret();
        }

        return $this->secret;
    }

    public function setSecret(string $secret): void
    {
        $secretObj = new Secret(1, $secret);
        $this->secretRepository->set($secretObj);
        $this->secret = $secret;
    }

    private function readSecret(): void
    {
        /** @var Secret $secretObj */
        $secretObj = $this->secretRepository->getById(1);
        if (!$secretObj) {
            $this->generateNewSecret();
            return;
        }

        $this->secret = $secretObj->getSecret();
    }

    public function generateNewSecret(): void
    {
        $this->setSecret(md5(bin2hex(random_bytes(256))));
    }
}