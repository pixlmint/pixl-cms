<?php

namespace PixlMint\CMS\Repository;

use Nacho\ORM\AbstractRepository;
use PixlMint\CMS\Model\NavCache;

class NavCacheRepository extends AbstractRepository
{
    public static function getDataName(): string
    {
        return 'nav_cache';
    }

    protected static function getModel(): string
    {
        return NavCache::class;
    }
}
