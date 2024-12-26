<?php

namespace PixlMint\CMS\Controllers;

use DateTime;
use Nacho\Contracts\PageManagerInterface;
use Nacho\Contracts\RequestInterface;
use Nacho\Models\HttpResponse;
use Nacho\Nacho;
use PixlMint\CMS\Helpers\CustomUserHelper;
use PixlMint\CMS\Helpers\BackupHelper;
use Nacho\Controllers\AbstractController;
use Nacho\Models\HttpMethod;
use PixlMint\CMS\Contracts\AdminControllerInterface;
use PixlMint\CMS\Helpers\Admin\BaseEntryHandler;
use PixlMint\CMS\Helpers\Admin\EditContentHandler;
use PixlMint\CMS\Helpers\Admin\PageSecurityHandler;
use PixlMint\JournalPlugin\Helpers\CacheHelper;

class AdminController extends AbstractController implements AdminControllerInterface
{
    private PageManagerInterface $pageManager;
    private RequestInterface $request;

    public function __construct(PageManagerInterface $pageManager, RequestInterface $request)
    {
        parent::__construct();
        $this->pageManager = $pageManager;
        $this->request = $request;
    }

    /**
     * GET:  fetch the markdown for a file
     * PUT:  save edited file
     */
    function edit(EditContentHandler $editContentHandler): HttpResponse
    {
        $page = $editContentHandler->handleEdit($this->request);

        if ($this->request->isMethod(HttpMethod::PUT)) {
            return $this->json([
                'message' => 'successfully saved content',
                'file' => $page->file,
                'lastUpdate' => (new DateTime())->format('Y-m-d H:i:s'),
            ]);
        } else {
            return $this->json($page->toArray());
        }
    }

    public function changePageSecurity(PageSecurityHandler $pageSecurityHandler): HttpResponse
    {
        return $this->json(['success' => $pageSecurityHandler->changePageSecurity()]);
    }

    public function addFolder(BaseEntryHandler $entryHandler): HttpResponse
    {
        $success = $entryHandler->addFolder();
        return $this->json(['success' => $success !== null]);
    }

    public function deleteFolder(BaseEntryHandler $entryHandler, RequestInterface $request): HttpResponse
    {
        return $this->delete($entryHandler, $request);
    }

    public function delete(BaseEntryHandler $entryHandler, RequestInterface $request): HttpResponse
    {
        $entry = $request->getBody()->get('entry');
        $success = $entryHandler->delete();

        if ($success) {
            return $this->json(['message' => "successfully deleted {$entry}"]);
        } else {
            return $this->json(['message' => "error deleting {$entry}"], 404);
        }
    }

    public function add(BaseEntryHandler $entryHandler): HttpResponse
    {
        $success = $entryHandler->addEntry();

        return $this->json(['success' => $success !== null]);
    }

    public function rename(BaseEntryHandler $entryHandler, RequestInterface $request): HttpResponse
    {
        $success = $entryHandler->rename();

        return $this->json(['success' => $success]);
    }

    public function fetchLastChanged(RequestInterface $request): HttpResponse
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }
        if (!$request->getBody()->has('entry')) {
            return $this->json(['message' => 'Please define the entry to fetch'], 400);
        }

        $entryId = $request->getBody()->get('entry');
        $entry = $this->pageManager->getPage($entryId);

        return $this->json(['lastChanged' => $entry->meta->dateUpdated]);
    }

    public function generateBackup(): HttpResponse
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }

        $backupHelper = Nacho::$container->get(BackupHelper::class);
        $zip = $backupHelper->generateBackup();

        return $this->json(['file' => $zip]);
    }

    public function restoreFromBackup(BackupHelper $backupHelper, CacheHelper $cacheHelper): HttpResponse
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }

        $zipPath = $_FILES['backup']['tmp_name'];
        $success = $backupHelper->restoreFromBackup($zipPath);
        $cacheHelper->build();

        return $this->json(['success' => $success]);
    }

    public function moveEntry(RequestInterface $request): HttpResponse
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }
        if (!$request->getBody()->has('entry') | !$request->getBody()->has('targetFolder')) {
            return $this->json(['message' => 'Please define the entry to move and the target Folder'], 400);
        }

        $entryId = $request->getBody()->get('entry');
        $targetFolderId = $request->getBody()->get('targetFolder');

        $success = $this->pageManager->move($entryId, $targetFolderId);

        return $this->json(['success' => $success], $success ? 200 : 400);
    }
}
