<?php

namespace PixlMint\CMS\Hooks;

use Nacho\Contracts\Hooks\PostFindRoute;
use Nacho\Models\Route;
use PixlMint\CMS\Helpers\CMSConfiguration;

/**
 * This hook checks if the user is trying to access a /api route. If not it changes the Controller to FrontendController
 */
class RouteCheckHook implements PostFindRoute
{
    private CMSConfiguration $cmsConfiguration;

    public function __construct(CMSConfiguration $cmsConfiguration)
    {
        $this->cmsConfiguration = $cmsConfiguration;
    }

    public function call(Route $route): Route
    {
        if (!str_starts_with($route->getPath(), 'api') && self::frontendControllerExists()) {
            $newRoute = [
                'route' => $route->getPath(),
                'controller' => $this->cmsConfiguration->frontendController(),
                'function' => 'index',
            ];

            $route = new Route($newRoute);
        }

        return $route;
    }

    private function frontendControllerExists(): bool
    {
        $frontendController = $this->cmsConfiguration->frontendController();

        return !is_null($frontendController);
    }
}