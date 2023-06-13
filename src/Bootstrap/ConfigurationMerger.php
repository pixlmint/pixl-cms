<?php

namespace PixlMint\CMS\Bootstrap;

class ConfigurationMerger
{
    private array $routes = [];
    private array $config = [];
    private array $cmsConfig;
    private array $siteConfig;
    private array $pluginsConfig;

    public function __construct(array $cmsConfig, array $siteConfig, array $pluginsConfig)
    {
        $this->cmsConfig = $cmsConfig;
        $this->siteConfig = $siteConfig;
        $this->pluginsConfig = $pluginsConfig;
    }

    public function merge(): array
    {
        $this->mergeConfigs([$this->cmsConfig, $this->siteConfig]);
        $this->mergeConfigs($this->pluginsConfig);

        $this->config['routes'] = $this->mergeRoutes();

        return $this->config;
    }

    private function mergeConfigs(array $configs): void
    {
        foreach ($configs as $confArr) {
            foreach ($confArr as $key => $tmpConf) {
                if ($key === 'hooks') {
                    if (key_exists('hooks', $this->config)) {
                        $this->config['hooks'] = array_merge($this->config['hooks'], $tmpConf);
                    } else {
                        $this->config['hooks'] = $tmpConf;
                    }
                } elseif ($key === 'routes') {
                    $this->routes = array_merge($this->routes, $tmpConf);
                } else {
                    $this->config[$key] = $tmpConf;
                }
            }
        }
    }

    private function mergeRoutes(): array
    {
        $finalRoutes = [];

        foreach ($this->routes as $route) {
            $routeIndex = self::findRouteIndex($finalRoutes, $route['route']);
            if ($routeIndex === -1) {
                $finalRoutes[] = $route;
            } else {
                $finalRoutes[$routeIndex] = $route;
            }
        }

        return $finalRoutes;
    }

    private static function findRouteIndex(array $routes, string $route): int
    {
        for ($i = 0; $i < count($routes); $i++) {
            if ($routes[$i]['route'] === $route) {
                return $i;
            }
        }

        return -1;
    }
}