<?php

namespace PixlMint\CMS\Controllers;

use PixlMint\CMS\Actions\RenameAction;
use PixlMint\CMS\Helpers\ContentHelper;
use PixlMint\CMS\Helpers\CustomUserHelper;
use PixlMint\CMS\Helpers\BackupHelper;
use Nacho\Controllers\AbstractController;
use Nacho\Models\HttpMethod;
use Nacho\Models\HttpResponseCode;
use Nacho\Models\Request;
use Nacho\Nacho;
use PixlMint\JournalPlugin\Helpers\CacheHelper;

class AdminController extends AbstractController
{
    private ContentHelper $contentHelper;

    public function __construct(Nacho $nacho)
    {
        parent::__construct($nacho);
        $this->contentHelper = new ContentHelper($nacho->getMarkdownHelper());
    }

    /**
     * GET:  fetch the markdown for a file
     * POST: save edited file
     */
    function edit(Request $request): string
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }
        $strPage = $request->getBody()['entry'];
        $page = $this->nacho->getMarkdownHelper()->getPage($strPage);

        if (!$page || !is_file($page->file)) {
            return $this->json(['message' => 'Unable to find this file']);
        }

        if (strtoupper($request->requestMethod) === HttpMethod::PUT) {
            $meta = [];
            if (key_exists('meta', $request->getBody())) {
                $meta = $request->getBody()['meta'];
            }
            $now = new \DateTime();
            $meta['lastEdited'] = $now->format('Y-m-d H:i:s');
            $content = $request->getBody()['content'];
            $this->nacho->getMarkdownHelper()->editPage($page->id, $content, $meta);

            return $this->json(['message' => 'successfully saved content', 'file' => $page->file]);
        }

        return $this->json((array)$page);
    }

    public function addFolder(): string
    {
        $parentFolder = $_REQUEST['parentFolder'];
        $folderName = $_REQUEST['folderName'];
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }

        $success = $this->contentHelper->create($parentFolder, $folderName, true);

        return $this->json(['success' => $success]);
    }

    public function deleteFolder(Request $request): string
    {
        return $this->delete($request);
    }

    function add(): string
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }
        $title = $_REQUEST['title'];
        $parentFolder = $_REQUEST['parentFolder'];

        $success = $this->contentHelper->create($parentFolder, $title);

        return $this->json(['success' => $success]);
    }

    // TODO This shouldn't just change the title but also the entry ID
    function rename(Request $request): string
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }
        if (!key_exists('entry', $_GET) || !key_exists('new-title', $_GET)) {
            return $this->json(['message' => 'Please define entry and content'], HttpResponseCode::BAD_REQUEST);
        }
        if (strtoupper($request->requestMethod) !== HttpMethod::PUT) {
            return $this->json(['message' => 'Only PUT allowed'], HttpResponseCode::METHOD_NOT_ALLOWED);
        }

        RenameAction::setMarkdownHelper($this->nacho->getMarkdownHelper());
        $success = RenameAction::run($_GET);

        return $this->json(['success' => $success]);
    }

    public function delete(Request $request): string
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }
        if (!key_exists('entry', $request->getBody())) {
            return $this->json(['message' => 'Please define the entry to delete'], 400);
        }

        $entry = $request->getBody()['entry'];

        $success = $this->contentHelper->delete($entry);

        if ($success) {
            return $this->json(['message' => "successfully deleted ${entry}"]);
        } else {
            return $this->json(['message' => "error deleting ${entry}"], 404);
        }
    }

    public function generateBackup(): string
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }

        $backupHelper = new BackupHelper();
        $zip = $backupHelper->generateBackup();

        return $this->json(['file' => $zip]);
    }

    public function restoreFromBackup(Request $request): string
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }

        $zipPath = $_FILES['backup']['tmp_name'];
        $backupHelper = new BackupHelper();
        $success = $backupHelper->restoreFromBackup($zipPath);
        $cacheHelper = new CacheHelper($this->nacho);
        $cacheHelper->build();

        return $this->json(['success' => $success]);
    }
}
