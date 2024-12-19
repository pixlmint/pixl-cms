<?php

namespace PixlMint\CMS\Controllers;

use Nacho\Contracts\RequestInterface;
use Nacho\Controllers\AbstractController;
use Nacho\Models\HttpResponse;
use PixlMint\CMS\Helpers\NavRenderer;

class NavController extends AbstractController
{
    public function loadNav(NavRenderer $navRenderer, RequestInterface $request): HttpResponse
    {
        $forceRerender = false;
        if ($request->getBody()->has('forceReload')) {
            $forceRerender = true;
        }
        return $this->json($navRenderer->loadNav([], $forceRerender));
    }
}
