<?php

namespace PixlMint\CMS\Helpers;

use Nacho\Exceptions\ConfigurationValueDoesNotExistException;
use Nacho\Helpers\ConfigurationContainer;
use Nacho\Nacho;

class CMSConfiguration
{
    private ConfigurationContainer $configurationContainer;

    public function __construct(ConfigurationContainer $configurationContainer)
    {
        $this->configurationContainer = $configurationContainer;
    }

    public function mediaDir(): string
    {
        return $this->getConfigValue('mediaDir');
    }

    public function mediaBaseUrl(): string
    {
        return $this->getConfigValue('mediaBaseUrl');
    }

    public function version(): string|int|float
    {
        return $this->getConfigValue('version');
    }

    public function contentDir(): string
    {
        return $this->getConfigValue('contentDir');
    }

    public function dataDir(): string
    {
        return $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'data';
    }

    public function frontendController(): ?string
    {
        return $this->getConfigValueSafe('frontendController');
    }

    public function debugEnabled(): bool
    {
        return $this->getConfigValueSafe('debugEnabled') ?? false;
    }

    private function getConfigValue(string $confName): mixed
    {
        if (!key_exists($confName, $this->configurationContainer->getCustomConfig('base'))) {
            throw new ConfigurationValueDoesNotExistException($confName, 'base');
        } else {
            return $this->configurationContainer->getCustomConfig('base')[$confName];
        }
    }

    private function getConfigValueSafe(string $confName): mixed
    {
        try {
            return $this->getConfigValue($confName);
        } catch (ConfigurationValueDoesNotExistException $e) {
            return null;
        }
    }
}
