<?php

namespace PixlMint\CMS\Controllers;

use Nacho\Contracts\RequestInterface;
use Nacho\Controllers\AbstractController;
use Nacho\Helpers\PageManager;
use Nacho\Models\HttpResponse;
use Nacho\Models\Request;
use PixlMint\CMS\Helpers\CustomUserHelper;

class AlternativeContentController extends AbstractController
{
    private PageManager $pageManager;

    public function __construct(PageManager $pageManager)
    {
        parent::__construct();
        $this->pageManager = $pageManager;
    }

    public function update(RequestInterface $request): HttpResponse
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }

        $meta = json_decode($request->getBody()['meta'], true);
        $success = $this->pageManager->editPage($request->getBody()['entry'], '', $meta);

        return $this->json(['success' => $success]);
    }

    public function upload(RequestInterface $request): HttpResponse
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }
        if (!key_exists('title', $request->getBody()) || !key_exists('parentFolder', $request->getBody()) || !key_exists('renderer', $request->getBody())) {
            return $this->json(['define title, parentFolder and renderer'], 400);
        }

        $parentFolder = $request->getBody()['parentFolder'];
        $title = '.' . $request->getBody()['title'];
        $renderer = $request->getBody()['renderer'];

        $page = $this->pageManager->create($parentFolder, $title);

        if ($page === null) {
            return $this->json(['success' => false]);
        }

        $this->pageManager->readPages();

        $title = $page->meta->title;
        $title = ltrim($title, '.');

        $newMeta = [
            'renderer' => $renderer,
            'title' => $title,
            'kind' => $renderer,
        ];

        $success = $this->pageManager->editPage($page->id, '', $newMeta);

        if ($success) {
            return $this->json(['success' => true, 'id' => $page->id]);
        } else {
            return $this->json(['success' => false], 500);
        }
    }
}
