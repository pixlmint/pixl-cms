<?php

namespace PixlMint\CMS;

use Nacho\Bootstrap\ConfigurationMerger;
use Nacho\Contracts\UserHandlerInterface;
use Nacho\Models\ContainerDefinitionsHolder;
use Nacho\Nacho;
use Nacho\Helpers\HookHandler;
use PixlMint\CMS\Anchors\InitAnchor;
use PixlMint\CMS\Helpers\CustomExceptionHandler;
use PixlMint\CMS\Helpers\CustomUserHelper;
use PixlMint\CMS\Helpers\SecretHelper;
use function DI\create;

class CmsCore
{
    // private array $cmsConfig;

    public function init(): void
    {
        $core = new Nacho();
        $containerBuilder = $core->getContainerBuilder();
        // $this->cmsConfig = $this->loadConfig();

        /*if (!$this->cmsConfig['base']['debugEnabled']) {
            $containerBuilder->enableCompilation('var/cache');
        }*/
        $containerBuilder->addDefinitions($this->getContainerDefinitions());

        $core->init($containerBuilder);

        /*if (!Nacho::$container->get('debug')) {
            error_reporting(E_ERROR | E_PARSE);
            set_exception_handler([new CustomExceptionHandler(), 'handleException']);
        }*/

        Nacho::$container->get(HookHandler::class)->registerAnchor(InitAnchor::getName(), new InitAnchor());

        $core->run(require_once('config/config.php'));
    }

    private function loadConfig(): array {
        $siteConfigPath = 'config/config.php';
        $cmsConfigPath = $this->getCMSDirectory() . 'config/config.php';

        $merger = new ConfigurationMerger(require($cmsConfigPath), require($siteConfigPath) );
        return $merger->merge();
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
            // 'debug' => $this->cmsConfig['base']['debugEnabled'],
            'debug' => true,
            SecretHelper::class => create(SecretHelper::class),
        ]);
    }
}

