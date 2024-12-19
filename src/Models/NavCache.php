<?php

namespace PixlMint\CMS\Models;

use Nacho\Contracts\ArrayableInterface;
use Nacho\ORM\AbstractModel;
use Nacho\ORM\ModelInterface;

class NavCache extends AbstractModel implements ModelInterface, ArrayableInterface
{
    protected array $nav = [];

    public function setNav(array $nav): void
    {
        $this->nav = $nav;
    }

    public function getNav(): array
    {
        return $this->nav;
    }

    public function toArray(): array
    {
        return [
            'nav' => $this->nav,
        ];
    }
}
