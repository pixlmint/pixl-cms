<?php

namespace PixlMint\CMS\Helpers;

class CustomExceptionHandler
{
    public function handleException($e): void
    {
        $response = ['exception' => $e->getMessage()];
        header("HTTP/1.1 500");
        header("content-type: application/json");

        echo json_encode($response);
    }
}