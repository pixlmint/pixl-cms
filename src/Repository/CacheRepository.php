<?php

namespace PixlMint\CMS\Repository;

use PixlMint\CMS\Models\Cache;
use Nacho\ORM\AbstractRepository;

class CacheRepository extends AbstractRepository
{
    public static function getDataName(): string
    {
        return 'cache';
    }

    protected static function getModel(): string
    {
        return Cache::class;
    }
}
