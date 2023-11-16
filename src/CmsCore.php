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
use PixlMint\CMS\Helpers\SecretHelper;
use PixlMint\JournalPlugin\Helpers\CacheHelper;
use function DI\create;

class CmsCore
{
    private array $plugins = [];
    private array $config = [];

    public function init($config = []): void
    {
        if (!$config) {
            $this->config = self::loadConfig();
        } else {
            $this->config = $config;
        }

        if (!$this->config['base']['debugEnabled']) {
            error_reporting(E_ERROR | E_PARSE);
            set_exception_handler([new CustomExceptionHandler(), 'handleException']);
        }
        $core = new Nacho();
        $containerBuilder = $core->getContainerBuilder();
        $containerBuilder->addDefinitions($this->getContainerDefinitions());

        $core->init($containerBuilder);

        Nacho::$container->get(HookHandler::class)->registerAnchor(InitAnchor::getName(), new InitAnchor());

        $core->run($this->config);
    }

    private function loadConfig(): array
    {
        $cmsConfig = require_once(self::getCMSDirectory() . 'config' . DIRECTORY_SEPARATOR . 'config.php');
        $siteConfig = require_once('config/config.php');

        if (key_exists('plugins', $siteConfig)) {
            $this->plugins = $siteConfig['plugins'];
        }

        $pluginConfig = self::loadPluginsConfig();

        $configMerger = new ConfigurationMerger($cmsConfig, $siteConfig, $pluginConfig);

        return $configMerger->merge();
    }

    private function loadPluginsConfig(): array
    {
        $ret = [];
        foreach ($this->plugins as $plugin) {
            if ($this->isPluginEnabled($plugin)) {
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
            UserHandlerInterface::class => create(CustomUserHelper::class)->constructor(
                SecretHelper::class,
            ),
            'debug' => $this->config['base']['debugEnabled'],
            SecretHelper::class => create(SecretHelper::class),
            CacheHelper::class => create(CacheHelper::class),
        ]);
    }
}