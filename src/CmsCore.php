<?php

namespace PixlMint\CMS;

use Nacho\Core;
use Nacho\Helpers\HookHandler;
use PixlMint\CMS\Anchors\InitAnchor;
use PixlMint\CMS\Bootstrap\ConfigurationMerger;

class CmsCore
{
    private static array $plugins = [];

    public static function init(): void
    {
        $config = self::loadConfig();
        HookHandler::getInstance()->registerAnchor(InitAnchor::getName(), new InitAnchor());
        Core::getInstance()->run($config);
    }

    private static function loadConfig(): array
    {
        $cmsConfig = require_once(self::getCMSDirectory() . 'config' . DIRECTORY_SEPARATOR . 'config.php');
        $siteConfig = require_once('config/config.php');

        if (key_exists('plugins', $siteConfig)) {
            self::$plugins = $siteConfig['plugins'];
        }

        $pluginConfig = self::loadPluginsConfig();

        $configMerger = new ConfigurationMerger($cmsConfig, $siteConfig, $pluginConfig);

        return $configMerger->merge();
    }

    private static function loadPluginsConfig(): array
    {
        $ret = [];
        foreach (self::$plugins as $plugin) {
            if (self::isPluginEnabled($plugin)) {
                $ret[$plugin['name']] = $plugin['config'];
            }
        }

        return $ret;
    }

    private static function isPluginEnabled(array $plugin): bool
    {
        return (key_exists('enabled', $plugin) && $plugin['enabled']) || !key_exists('enabled', $plugin);
    }

    private static function getCMSDirectory(): string
    {
        $cmsDirectory = getenv('CMS_DIR');
        if ($cmsDirectory) {
            return $cmsDirectory;
        } else {
            return 'vendor/pixlmint/pixl-cms/';
        }
    }
}