<?php

namespace PixlMint\CMS;

use DI\ContainerBuilder;
use Nacho\Contracts\UserHandlerInterface;
use Nacho\Models\ContainerDefinitionsHolder;
use Nacho\Nacho;
use Nacho\Helpers\HookHandler;
use PixlMint\CMS\Anchors\InitAnchor;
use PixlMint\CMS\Bootstrap\ConfigurationMerger;
use PixlMint\CMS\Helpers\CustomExceptionHandler;
use PixlMint\CMS\Helpers\CustomUserHelper;
use function DI\create;

class CmsCore
{
    private static array $plugins = [];

    public function init($config = []): void
    {
        if (!$config) {
            $config = self::loadConfig();
        }

        if (!$config['base']['debugEnabled']) {
            error_reporting(E_ERROR | E_PARSE);
            set_exception_handler([new CustomExceptionHandler(), 'handleException']);
        }
        $core = new Nacho();
        $containerBuilder = $core->getContainerBuilder();
        $containerBuilder->addDefinitions($this->getContainerDefinitions());

        $core->init($containerBuilder);

        Nacho::$container->get(HookHandler::class)->registerAnchor(InitAnchor::getName(), new InitAnchor());

        $core->run($config);
    }

    private function loadConfig(): array
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

    private function loadPluginsConfig(): array
    {
        $ret = [];
        foreach (self::$plugins as $plugin) {
            if (self::isPluginEnabled($plugin)) {
                $ret[$plugin['name']] = $plugin['config'];
            }
        }

        return $ret;
    }

    private function isPluginEnabled(array $plugin): bool
    {
        return (key_exists('enabled', $plugin) && $plugin['enabled']) || !key_exists('enabled', $plugin);
    }

    private function getCMSDirectory(): string
    {
        $cmsDirectory = getenv('CMS_DIR');
        if ($cmsDirectory) {
            return $cmsDirectory;
        } else {
            return 'vendor/pixlmint/pixl-cms/';
        }
    }

    private function getContainerDefinitions(): ContainerDefinitionsHolder
    {
        return new ContainerDefinitionsHolder(2, [
            UserHandlerInterface::class => create(CustomUserHelper::class),
        ]);
    }
}