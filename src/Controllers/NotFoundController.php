<?php

namespace PixlMint\CMS\Controllers;

use Nacho\Controllers\AbstractController;

class NotFoundController extends AbstractController
{
    function index()
    {
        return $this->json(['message' => 'Route not found'], 404);
    }
}