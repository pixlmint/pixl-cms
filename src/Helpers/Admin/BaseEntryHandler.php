<?php

namespace PixlMint\CMS\Helpers\Admin;

use Nacho\Contracts\PageManagerInterface;
use Nacho\Contracts\RequestInterface;
use Nacho\Contracts\UserHandlerInterface;
use Nacho\Exceptions\BadRequestHttpException;
use Nacho\Exceptions\MethodNotAllowedHttpException;
use Nacho\Exceptions\UnauthorizedHttpException;
use Nacho\Models\HttpMethod;
use PixlMint\CMS\Actions\RenameAction;
use PixlMint\CMS\Helpers\CustomUserHelper;

class BaseEntryHandler
{
    private RequestInterface $request;
    private PageManagerInterface $pageManager;
    private UserHandlerInterface $userHandler;

    public function __construct(RequestInterface $request, PageManagerInterface $pageManager, UserHandlerInterface $userHandler)
    {
        $this->request = $request;
        $this->pageManager = $pageManager;
        $this->userHandler = $userHandler;
    }

    public function addFolder()
    {
        if (!$this->userHandler->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            throw new UnauthorizedHttpException("You are not authenticated");
        }

        $parentFolder = $this->request->getBody()->get('parentFolder');
        $folderName = $this->request->getBody()->get('folderName');
        if (!$this->userHandler->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            throw new UnauthorizedHttpException();
        }

        return $this->pageManager->create($parentFolder, $folderName, true);
    }

    public function addEntry()
    {
        $title = $this->request->getBody()->get('title');
        $parentFolder = $this->request->getBody()->get('parentFolder');

        return $this->pageManager->create($parentFolder, $title);
    }

    public function delete()
    {
        if (!$this->userHandler->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            throw new UnauthorizedHttpException("You are not authenticated");
        }
        if (!$this->request->getBody()->has('entry')) {
            throw new BadRequestHttpException("Define the entry to be deleted");
        }

        $entry = $this->request->getBody()->get('entry');

        return $this->pageManager->delete($entry);
    }

    public function rename()
    {
        if (!$this->userHandler->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            throw new UnauthorizedHttpException("You are not authenticated");
        }
        if (!$this->request->getBody()->has('entry') || !$this->request->getBody()->has('new-title')) {
            throw new BadRequestHttpException("Define entry and content");
        }
        if (!$this->request->isMethod(HttpMethod::PUT)) {
            throw new MethodNotAllowedHttpException("Only PUT allowed");
        }
        $args = [];
        foreach ($this->request->getBody()->keys() as $key) {
            $args[$key] = $this->request->getBody()->get($key);
        }

        return RenameAction::run($args);
    }
}
