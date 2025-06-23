<?php

namespace PixlMint\CMS\Helpers;

use Nacho\Exceptions\ConfigurationDoesNotExistException;
use Nacho\Helpers\ConfigurationContainer;

abstract class PluginConfiguration
{
    protected ConfigurationContainer $configurationContainer;

    public function __construct(ConfigurationContainer $configurationContainer)
    {
        $this->configurationContainer = $configurationContainer;
    }

    protected function getConfigValue(string $key): mixed
    {
        $config = $this->configurationContainer->getCustomConfig($this->getPluginConfigKey());

        if (!key_exists($key, $config)) {
            throw new ConfigurationDoesNotExistException("{$key} does not exist in {$this->getPluginConfigKey()} configuration");
        }

        return $config[$key];
    }

    protected function getConfigValueOrFallback(string $key, mixed $default = null): mixed
    {
        $config = $this->configurationContainer->getCustomConfig($this->getPluginConfigKey());

        if (!key_exists($key, $config)) {
            return $default;
        }

        return $config[$key];
    }

    protected abstract function getPluginConfigKey(): string;
}
