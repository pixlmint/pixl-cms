<?php

namespace PixlMint\CMS;

use Nacho\Contracts\UserHandlerInterface;
use Nacho\Models\ContainerDefinitionsHolder;
use Nacho\Nacho;
use Nacho\Helpers\HookHandler;
use PixlMint\CMS\Anchors\InitAnchor;
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

        $containerBuilder->addDefinitions($this->getContainerDefinitions());

        $core->init($containerBuilder);

        Nacho::$container->get(HookHandler::class)->registerAnchor(InitAnchor::getName(), new InitAnchor());

        $core->run(require_once('config/config.php'));
    }

    private function getContainerDefinitions(): ContainerDefinitionsHolder
    {
        return new ContainerDefinitionsHolder(2, [
            UserHandlerInterface::class => create(CustomUserHelper::class),
            'debug' => false,
            SecretHelper::class => create(SecretHelper::class),
        ]);
    }
}

