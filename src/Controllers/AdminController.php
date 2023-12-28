<?php

namespace PixlMint\CMS\Controllers;

use DateTime;
use Nacho\Contracts\PageManagerInterface;
use Nacho\Contracts\RequestInterface;
use Nacho\Helpers\PicoVersioningHelper;
use Nacho\Helpers\Utils;
use Nacho\Models\HttpResponse;
use Nacho\Nacho;
use PixlMint\CMS\Actions\RenameAction;
use PixlMint\CMS\Helpers\CustomUserHelper;
use PixlMint\CMS\Helpers\BackupHelper;
use Nacho\Controllers\AbstractController;
use Nacho\Models\HttpMethod;
use Nacho\Models\HttpResponseCode;
use PixlMint\CMS\Helpers\TokenHelper;
use PixlMint\JournalPlugin\Helpers\CacheHelper;

class AdminController extends AbstractController
{
    private PageManagerInterface $pageManager;

    public function __construct(PageManagerInterface $pageManager)
    {
        parent::__construct();
        $this->pageManager = $pageManager;
    }

    /**
     * GET:  fetch the markdown for a file
     * PUT: save edited file
     */
    function edit(RequestInterface $request, PicoVersioningHelper $versioningHelper): HttpResponse
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }
        if (!key_exists('entry', $request->getBody())) {
            return $this->json(['message' => 'Please define the entry'], HttpResponseCode::BAD_REQUEST);
        }
        if (strtoupper($request->requestMethod) === HttpMethod::PUT && (!key_exists('lastUpdate', $request->getBody()) || !key_exists('content', $request->getBody()))) {
            return $this->json(['message' => 'Please define content and lastUpdate arguments'], HttpResponseCode::BAD_REQUEST);
        }
        $meta = $request->getBody()['meta'];
        if (Utils::isJson($meta)) {
            $request->getBody()['meta'] = json_decode($meta, TRUE);
        }
        $strPage = $request->getBody()['entry'];
        $page = $this->pageManager->getPage($strPage);

        if (!$page || !is_file($page->file)) {
            return $this->json(['message' => 'Unable to find this file']);
        }

        if (strtoupper($request->requestMethod) === HttpMethod::PUT) {
            $meta = [];
            if (key_exists('meta', $request->getBody())) {
                $meta = $request->getBody()['meta'];
                if (Utils::isJson($meta)) {
                    $meta = json_decode($meta, TRUE);
                }
            }
            $lastUpdateTime = $page->meta->dateUpdated;
            if (is_numeric($lastUpdateTime)) {
                $lastUpdateTime = date('Y-m-d H:i:s', $lastUpdateTime);
            }
            if (!$versioningHelper->hasValidUpdateTime($request)) {
                return $this->json([
                    'message' => 'Invalid lastUpdate date supplied: ' . $request->getBody()['lastUpdate'],
                ], HttpResponseCode::BAD_REQUEST);
            }
            if (!$versioningHelper->canUpdateToVersion($page, $request->getBody()['lastUpdate'])) {
                return $this->json([
                    'message' => 'This page has already been updated by another client more recently',
                    'lastUpdate' => $lastUpdateTime,
                ], HttpResponseCode::CONFLICT);
            }
            $content = $request->getBody()['content'];
            $this->pageManager->editPage($page->id, $content, $meta);

            return $this->json([
                'message' => 'successfully saved content',
                'file' => $page->file,
                'lastUpdate' => (new DateTime())->format('Y-m-d H:i:s'),
            ]);
        }

        return $this->json((array)$page);
    }

    public function loadMarkdownFile(RequestInterface $request, CustomUserHelper $userHelper, TokenHelper $tokenHelper): HttpResponse
    {
        $token = null;
        $users = $userHelper->getUsers();
        if (key_exists('token', $request->getBody())) {
            $token = $request->getBody()['token'];
        }
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR) && !$tokenHelper->isTokenValid($token, $users)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }
        if (!key_exists('entry', $request->getBody())) {
            return $this->json(['message' => 'Please define the entry'], 400);
        }

        $pageId = $request->getBody()['entry'];
        $page = $this->pageManager->getPage($pageId);

        if (!$page) {
            return $this->json(['message' => 'Unable to find this page'], 404);
        }

        $md = $page->raw_markdown;

        return new HttpResponse($md, 200, [
            'content-type' => 'text/plain',
        ]);
    }

    public function changePageSecurity(RequestInterface $request): HttpResponse
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }
        if (!key_exists('entry', $request->getBody()) || !key_exists('new_state', $request->getBody())) {
            return $this->json(['message' => 'Please define entry and new_state'], 400);
        }

        $pageId = $request->getBody()['entry'];
        $newState = $request->getBody()['new_state'];

        $page = $this->pageManager->getPage($pageId);

        if (!$page) {
            return $this->json(['message' => 'Unable to find this page'], 404);
        }

        $content = $page->raw_content;
        $meta = $page->meta;
        $meta->security = $newState;

        $success = $this->pageManager->editPage($pageId, $content, (array)$meta);

        return $this->json(['success' => $success]);
    }

    public function addFolder(): HttpResponse
    {
        $parentFolder = $_REQUEST['parentFolder'];
        $folderName = $_REQUEST['folderName'];
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }

        $success = $this->pageManager->create($parentFolder, $folderName, true);

        return $this->json(['success' => $success !== null]);
    }

    public function deleteFolder(RequestInterface $request): HttpResponse
    {
        return $this->delete($request);
    }

    function add(): HttpResponse
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }
        $title = $_REQUEST['title'];
        $parentFolder = $_REQUEST['parentFolder'];

        $success = $this->pageManager->create($parentFolder, $title);

        return $this->json(['success' => $success !== null]);
    }

    // TODO This shouldn't just change the title but also the entry ID
    function rename(RequestInterface $request): HttpResponse
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }
        if (!key_exists('entry', $request->getBody()) || !key_exists('new-title', $request->getBody())) {
            return $this->json(['message' => 'Please define entry and content'], HttpResponseCode::BAD_REQUEST);
        }
        if (strtoupper($request->requestMethod) !== HttpMethod::PUT) {
            return $this->json(['message' => 'Only PUT allowed'], HttpResponseCode::METHOD_NOT_ALLOWED);
        }

        $success = RenameAction::run($request->getBody());

        return $this->json(['success' => $success]);
    }

    public function fetchLastChanged(RequestInterface $request): HttpResponse
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }
        if (!key_exists('entry', $request->getBody())) {
            return $this->json(['message' => 'Please define the entry to fetch'], 400);
        }

        $entryId = $request->getBody()['entry'];
        $entry = $this->pageManager->getPage($entryId);

        return $this->json(['lastChanged' => $entry->meta->dateUpdated]);
    }

    public function delete(RequestInterface $request): HttpResponse
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }
        if (!key_exists('entry', $request->getBody())) {
            return $this->json(['message' => 'Please define the entry to delete'], 400);
        }

        $entry = $request->getBody()['entry'];

        $success = $this->pageManager->delete($entry);

        if ($success) {
            return $this->json(['message' => "successfully deleted ${entry}"]);
        } else {
            return $this->json(['message' => "error deleting ${entry}"], 404);
        }
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
}
