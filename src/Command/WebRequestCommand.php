<?php

namespace PixlMint\CMS\Command;

use Nacho\Nacho;

class WebRequestCommand
{
    private Nacho $core;

    public function __construct(Nacho $nacho)
    {
        $this->core = $nacho;
    }

    public function call(string $path, string $method = 'GET')
    {
        $_SERVER['REDIRECT_URL'] = $path;
        $_SERVER['REQUEST_METHOD'] = $method;
        $this->core->run();
    }
}
