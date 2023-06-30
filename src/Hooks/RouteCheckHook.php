<?php

namespace PixlMint\CMS\Hooks;

use Nacho\Exceptions\ConfigurationDoesNotExistException;
use Nacho\Helpers\ConfigurationHelper;
use Nacho\Contracts\Hooks\PostFindRoute;
use Nacho\Hooks\AbstractHook;
use Nacho\Models\Route;

/**
 * This hook checks if the user is trying to access a /api route. If not it changes the Controller to FrontendController
 */
class RouteCheckHook extends AbstractHook implements PostFindRoute
{
    public function call(Route $route): Route
    {
        if (!str_starts_with($route->getPath(), 'api') && self::frontendControllerExists()) {
            $newRoute = [
                'route' => $route->getPath(),
                'controller' => $this->getFrontendController(),
                'function' => 'index',
            ];

            $route = new Route($newRoute);
        }

        return $route;
    }

    private function getFrontendController(): string
    {
        $baseConfig = ConfigurationHelper::getInstance()->getCustomConfig('base');

        return $baseConfig['frontendController'];
    }

    private static function frontendControllerExists(): bool
    {
        $baseConfig = ConfigurationHelper::getInstance()->getCustomConfig('base');

        return key_exists('frontendController', $baseConfig);
    }
}