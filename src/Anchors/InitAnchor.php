<?php

namespace PixlMint\CMS\Anchors;

use Nacho\Contracts\Hooks\AnchorConfigurationInterface;
use Nacho\Hooks\AbstractAnchor;
use Nacho\Hooks\HookArgument;
use PixlMint\CMS\Contracts\InitFunction;

class InitAnchor extends AbstractAnchor implements AnchorConfigurationInterface
{
    public function __construct()
    {
        $this->arguments[] = new HookArgument('init', true);
    }

    public static function getName(): string
    {
        return 'init';
    }

    public static function getInterface(): string
    {
        return InitFunction::class;
    }

    public function exec(mixed $hook): void
    {
        if (!$hook instanceof InitFunction) {
            throw new \Exception('This is not a valid InitFunction Hook');
        }

        $this->arguments[0]->setValue($hook->call($this->arguments[0]->getValue()));
    }
}