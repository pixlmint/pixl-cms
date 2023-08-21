<?php

namespace PixlMint\CMS\Helpers;

use Nacho\Helpers\ConfigurationHelper;

class CMSConfiguration
{
    public static function mediaDir(): string
    {
        return self::getConfigValue('mediaDir');
    }

    public static function mediaBaseUrl(): string
    {
        return self::getConfigValue('mediaBaseUrl');
    }

    public static function version(): string|int|float
    {
        return self::getConfigValue('version');
    }

    public static function contentDir(): string
    {
        return self::getConfigValue('contentDir');
    }

    public static function dataDir(): string
    {
        return $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'data';
    }

    private static function getConfigValue(string $confName): mixed
    {
        return ConfigurationHelper::getInstance()->getCustomConfig('base')[$confName];
    }
}