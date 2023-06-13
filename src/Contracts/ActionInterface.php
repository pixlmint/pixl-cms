<?php

namespace PixlMint\CMS\Contracts;

interface ActionInterface
{
    public static function run(array $arguments):bool;
}