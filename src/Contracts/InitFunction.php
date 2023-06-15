<?php

namespace PixlMint\CMS\Contracts;

interface InitFunction
{
    public function call(array $init): array;
}