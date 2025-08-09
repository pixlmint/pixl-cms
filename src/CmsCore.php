<?php

namespace PixlMint\CMS;

use DI\Container;
use Nacho\Contracts\UserHandlerInterface;
use Nacho\Helpers\ConfigurationContainer;
use Nacho\Models\ContainerDefinitionsHolder;
use Nacho\Nacho;
use Nacho\Helpers\HookHandler;
use PixlMint\CMS\Anchors\InitAnchor;
use PixlMint\CMS\Command\CLI;
use PixlMint\CMS\Helpers\CustomUserHelper;
use PixlMint\CMS\Helpers\SecretHelper;
use function DI\create;

class CmsCore
{
    private array $userContainerDefinitions;

    public function __construct(array $userContainerDefinitions = [])
    {
        $this->userContainerDefinitions = $userContainerDefinitions;
    }

    public function init(): void
    {
        $core = $this->createCore();
        $core->run(require_once('config/config.php'));
    }

    public function cliInit(): CLI
    {
        $core = $this->createCore();
        $core->loadConfig(require_once('config/config.php'));

        $cli = Nacho::$container->get(CLI::class);

        $configurations = Nacho::$container->get(ConfigurationContainer::class);
        $commands = $configurations->getCustomConfig('commands');

        $cli->buildSpec($commands);

        return $cli;
    }

    private function createCore(): Nacho
    {
        $core = new Nacho();
        $containerBuilder = $core->getContainerBuilder();

        $containerBuilder->addDefinitions($this->getContainerDefinitions());

        $core->init($containerBuilder);

        Nacho::$container->get(HookHandler::class)->registerAnchor(InitAnchor::getName(), new InitAnchor());

        return $core;
    }

    private function getContainerDefinitions(): ContainerDefinitionsHolder
    {
        return new ContainerDefinitionsHolder(2, array_merge([
            UserHandlerInterface::class => create(CustomUserHelper::class),
            SecretHelper::class => create(SecretHelper::class),
        ], $this->userContainerDefinitions));
    }
}

