<?php

namespace PixlMint\CMS\Helpers;

use Exception;
use Nacho\Nacho;
use Psr\Log\LoggerInterface;

class CustomExceptionHandler
{
    public function handleException($e): void
    {
        try {
            Nacho::$container->get(LoggerInterface::class)->error('Caught message: ' . $e->getMessage());
        } catch (Exception $e) {
        }
        $response = ['exception' => $e->getMessage()];
        header("HTTP/1.1 500");
        header("content-type: application/json");

        echo json_encode($response);
    }
}