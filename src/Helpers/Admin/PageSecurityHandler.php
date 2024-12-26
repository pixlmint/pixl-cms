<?php

namespace PixlMint\CMS\Helpers\Admin;

use Nacho\Contracts\PageManagerInterface;
use Nacho\Contracts\RequestInterface;
use Nacho\Contracts\UserHandlerInterface;
use Nacho\Helpers\HookHandler;
use Nacho\Exceptions\BadRequestHttpException;
use Nacho\Exceptions\UnauthorizedHttpException;
use Nacho\Exceptions\NotFoundHttpException;
use Nacho\Hooks\NachoAnchors\PostHandleUpdateAnchor;

use PixlMint\CMS\Helpers\CustomUserHelper;

class PageSecurityHandler
{
    private HookHandler $hookHandler;
    private PageManagerInterface $pageManager;
    private RequestInterface $request;
    private UserHandlerInterface $userHandler;

    public function __construct(HookHandler $hookHandler, PageManagerInterface $pageManager, RequestInterface $request, UserHandlerInterface $userHandler)
    {
        $this->hookHandler = $hookHandler;
        $this->pageManager = $pageManager;
        $this->request = $request;
        $this->userHandler = $userHandler;
    }

    public function changePageSecurity(): bool
    {
        if (!$this->userHandler->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            throw new UnauthorizedHttpException();
        }
        if (!$this->request->getBody()->has('entry') || !$this->request->getBody()->has('new_state')) {
            throw new BadRequestHttpException('Define entry and new_state');
        }

        $pageId = $this->request->getBody()->get('entry');
        $newState = $this->request->getBody()->get('new_state');

        $page = $this->pageManager->getPage($pageId);

        if (!$page) {
            throw new NotFoundHttpException();
        }

        $content = $page->raw_content;
        $meta = $page->meta->toArray();
        $meta['security'] = $newState;

        $success = $this->pageManager->editPage($pageId, $content, $meta);

        $this->hookHandler->executeHook(PostHandleUpdateAnchor::getName(), ['entry' => $page]);

        return $success;
    }
}
