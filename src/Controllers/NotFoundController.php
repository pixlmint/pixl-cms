<?php

namespace PixlMint\CMS\Controllers;

use Nacho\Controllers\AbstractController;
use Nacho\Models\HttpResponse;

class NotFoundController extends AbstractController
{
    function index(): HttpResponse
    {
        return $this->json(['message' => 'Route not found'], 404);
    }
}