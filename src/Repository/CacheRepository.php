<?php

namespace PixlMint\CMS\Repository;

use PixlMint\CMS\Models\Cache;
use Nacho\ORM\AbstractRepository;
use Nacho\ORM\RepositoryInterface;

class CacheRepository extends AbstractRepository implements RepositoryInterface
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