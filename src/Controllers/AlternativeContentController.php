<?php

namespace PixlMint\CMS\Controllers;

use Nacho\Contracts\RequestInterface;
use Nacho\Controllers\AbstractController;
use Nacho\Helpers\HookHandler;
use Nacho\Helpers\PageManager;
use Nacho\Hooks\NachoAnchors\PostHandleUpdateAnchor;
use Nacho\Models\HttpResponse;
use Nacho\Models\HttpResponseCode;
use PixlMint\CMS\Helpers\CMSConfiguration;
use PixlMint\CMS\Helpers\CustomUserHelper;

class AlternativeContentController extends AbstractController
{
    private PageManager $pageManager;
    private HookHandler $hookHandler;

    public function __construct(PageManager $pageManager, HookHandler $hookHandler)
    {
        parent::__construct();
        $this->pageManager = $pageManager;
        $this->hookHandler = $hookHandler;
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

    // /api/entry/load-pdf
    public function loadPdf(RequestInterface $request, CMSConfiguration $config): HttpResponse
    {
        $token = $request->getBody()->getOrNull('pixltoken');
        if ($token) {
            $_SERVER['HTTP_PIXLTOKEN'] = $token;
        }
        $url = urldecode($request->getBody()->get('p'));
        $page = $this->pageManager->getPage($url);
        if (is_null($page)) {
            return $this->json(['message' => 'Unable to find Page ' . $url], HttpResponseCode::NOT_FOUND);
        }
        $pdfName = $page->meta->getAdditionalValues()->get('alternative_content');
        $absolutePath = $config->contentDir() . $page->meta->parentPath . '/' . $pdfName;

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment;filename="' . $pdfName . '"');
        readfile($absolutePath);

        return new HttpResponse('');
    }

    public function upload(RequestInterface $request): HttpResponse
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }
        if (!$request->getBody()->has('title') || !$request->getBody()->has('parentFolder') || !$request->getBody()->has('renderer')) {
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

        $this->hookHandler->executeHook(PostHandleUpdateAnchor::getName(), ['entry' => $page]);

        if ($success) {
            return $this->json(['success' => true, 'id' => $page->id]);
        } else {
            return $this->json(['success' => false], 500);
        }
    }
}
