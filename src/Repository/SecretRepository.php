<?php

namespace PixlMint\CMS\Repository;

use Nacho\ORM\AbstractRepository;
use Nacho\ORM\RepositoryInterface;
use PixlMint\CMS\Models\Secret;

class SecretRepository extends AbstractRepository implements RepositoryInterface
{
    public static function getDataName(): string
    {
        return 'secret';
    }

    protected static function getModel(): string
    {
        return Secret::class;
    }
}