<?php

namespace PixlMint\CMS\Controllers;

use Nacho\Controllers\AbstractController;
use Nacho\Exceptions\NotFoundHttpException;
use Nacho\Contracts\PageManagerInterface;
use Nacho\Contracts\RequestInterface;
use Nacho\Contracts\UserHandlerInterface;
use Nacho\Exceptions\BadRequestHttpException;
use Nacho\Exceptions\UnauthorizedHttpException;
use Nacho\Models\HttpResponse;
use PixlMint\CMS\Helpers\CustomUserHelper;
use PixlMint\CMS\Helpers\TokenHelper;

class RawMarkdownController extends AbstractController
{
    public function loadMarkdownFile(RequestInterface $request, UserHandlerInterface $userHandler, PageManagerInterface $pageManager, TokenHelper $tokenHelper): HttpResponse
    {
        $token = $request->getBody()->getOrNull('token');
        $users = $userHandler->getUsers();
        if (!$userHandler->isGranted(CustomUserHelper::ROLE_EDITOR) && !$tokenHelper->isTokenValid($token, $users)) {
            throw new UnauthorizedHttpException();
        }
        if (!$request->getBody()->has('entry')) {
            throw new BadRequestHttpException('Please define the entry');
        }

        $pageId = $request->getBody()->get('entry');
        $page = $pageManager->getPage($pageId);

        if (!$page) {
            throw new NotFoundHttpException('Unable to find this page');
        }

        $md = $page->raw_markdown;

        return new HttpResponse($md, 200, [
            'content-type' => 'text/plain',
        ]);
    }
}
