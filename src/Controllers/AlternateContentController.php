<?php

namespace PixlMint\CMS\Controllers;

use Nacho\Controllers\AbstractController;
use Nacho\Models\Request;
use PixlMint\CMS\Helpers\CustomUserHelper;

class AlternateContentController extends AbstractController
{
    public function update(Request $request): string
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }

        $helper = $this->nacho->getPageManager();
        $meta = json_decode($request->getBody()['meta'], true);
        $helper->editPage($request->getBody()['entry'], '', $meta);

        return $this->json(['success' => true]);
    }
}