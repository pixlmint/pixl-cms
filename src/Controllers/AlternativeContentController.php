<?php

namespace PixlMint\CMS\Controllers;

use Exception;
use Nacho\Contracts\RequestInterface;
use Nacho\Controllers\AbstractController;
use Nacho\Helpers\HookHandler;
use Nacho\Helpers\JupyterNotebookHelper;
use Nacho\Helpers\PageManager;
use Nacho\Helpers\PdfHelper;
use Nacho\Hooks\NachoAnchors\PostHandleUpdateAnchor;
use Nacho\Models\HttpResponse;
use Nacho\Models\HttpResponseCode;
use Nacho\Models\PicoPage;
use PixlMint\CMS\Helpers\CMSConfiguration;
use PixlMint\CMS\Helpers\CustomUserHelper;
use Psr\Log\LoggerInterface;

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

        $meta = $request->getBody()->get('meta');
        if (is_string($meta)) {
            $meta = json_decode($meta, true);
        }

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

    // /api/entry/load-jupyter-notebook
    public function loadJupyterNotebook(RequestInterface $request, CMSConfiguration $config): HttpResponse
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
        $notebookName = $page->meta->getAdditionalValues()->get('alternative_content');
        $absolutePath = $config->contentDir() . $page->meta->parentPath . '/' . $notebookName;

        $response = new HttpResponse(file_get_contents($absolutePath));

        $response->setHeader('Content-Type', 'application/json');

        return $response;
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

    /**
     * /api/admin/alternate/dump-file-into-content
     */
    public function dumpFileIntoContent(RequestInterface $request, PdfHelper $pdfHelper, JupyterNotebookHelper $notebookHelper, LoggerInterface $logger): HttpResponse
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }

        if ($request->getBody()->has('page')) {
            $page = $this->pageManager->getPage($request->getBody()->get('page'));
            $this->dumpSingleFileIntoContent($page, $pdfHelper, $notebookHelper);

            return $this->json(['message' => 'success']);
        } else {
            $pages = $this->pageManager->getPages();

            $success = [];
            $error = [];

            foreach ($pages as $page) {
                if (in_array($page->meta->renderer, ['pdf', 'ipynb'])) {
                    try {
                        $this->dumpSingleFileIntoContent($page, $pdfHelper, $notebookHelper);
                        $success[] = $page->id;
                    } catch (Exception $e) {
                        $error[$page->id] = $e->getMessage();
                    }
                }
            }
            return $this->json(['success' => $success, 'error' => $error]);
        }
    }

    private function dumpSingleFileIntoContent(PicoPage $page, PdfHelper $pdfHelper, JupyterNotebookHelper $notebookHelper)
    {
        $path = dirname($page->file) . DIRECTORY_SEPARATOR . $page->meta->alternative_content;

        if (is_file($path)) {
            switch ($page->meta->renderer) {
            case 'pdf':
                $content = $pdfHelper->getContent($path);
                break;
            case 'ipynb':
                $content = $notebookHelper->getContent($path);
                break;
            default:
                throw new Exception("Unknown renderer " . $page->meta->renderer);
            }
            $this->pageManager->editPage($page->id, $content, $page->meta->toArray());
        } else {
            throw new Exception("$path does not exist");
        }
    }
}
