<?php

namespace PixlMint\CMS\Controllers;

use PixlMint\CMS\Actions\RenameAction;
use PixlMint\CMS\Helpers\ContentHelper;
use PixlMint\CMS\Helpers\TokenHelper;
use PixlMint\CMS\Helpers\BackupHelper;
use PixlMint\CMS\Helpers\CacheHelper;
use PixlMint\CMS\Security\JsonUserHandler;
use Nacho\Controllers\AbstractController;
use Nacho\Models\HttpMethod;
use Nacho\Models\HttpResponseCode;
use Nacho\Models\Request;
use Nacho\Nacho;

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
    function edit(Request $request)
    {
        if (!key_exists('token', $_GET)) {
            return $this->json(['message' => 'You need to be authenticated'], 401);
        }
        $tokenHelper = new TokenHelper();
        $token = $_GET['token'];
        $user = $tokenHelper->isTokenValid($token, $this->nacho->getUserHandler()->getUsers());
        if (!$user) {
            return $this->json(['message' => 'The provided Token is invalid'], 401);
        }
        $strPage = $_GET['entry'];
        $page = $this->nacho->getMarkdownHelper()->getPage($strPage);

        if (!$page || !is_file($page->file)) {
            return $this->json(['message' => 'Unable to find this file']);
        }

        if (strtoupper($request->requestMethod) === HttpMethod::PUT) {
            $this->nacho->getMarkdownHelper()->editPage($page->id, $_GET['content'], []);
            $cacheHelper = new CacheHelper($this->nacho);
            $cacheHelper->build();

            return $this->json(['message' => 'successfully saved content', 'file' => $page->file]);
        }

        return $this->json((array) $page);
    }

    public function addFolder(): string
    {
        $token = $_REQUEST['token'];
        $parentFolder = $_REQUEST['parentFolder'];
        $folderName = $_REQUEST['folderName'];
        if (!$this->isGranted(JsonUserHandler::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }

        $success = $this->contentHelper->create($parentFolder, $folderName, true);

        return $this->json(['success' => $success]);
    }

    public function deleteFolder(Request $request): string
    {
        return $this->delete($request);
    }

    function add()
    {
        $token = $_REQUEST['token'];
        $title = $_REQUEST['title'];
        $parentFolder = $_REQUEST['parentFolder'];
        // TODO: token check?

        $success = $this->contentHelper->create($parentFolder, $title);

        return $this->json(['success' => $success]);
    }

    function rename(Request $request)
    {
        if (!key_exists('entry', $_GET) || !key_exists('new-title', $_GET) || !key_exists('token', $_GET)) {
            return $this->json(['message' => 'Please define entry and content'], HttpResponseCode::BAD_REQUEST);
        }
        if (strtoupper($request->requestMethod) !== HttpMethod::PUT) {
            return $this->json(['message' => 'Only PUT allowed'], HttpResponseCode::METHOD_NOT_ALLOWED);
        }

        RenameAction::setMarkdownHelper($this->nacho->getMarkdownHelper());
        $success = RenameAction::run($_GET);

        return $this->json(['success' => $success]);
    }

    public function delete(): string
    {
        if (!key_exists('token', $_REQUEST)) {
            return $this->json(['message' => 'You need to be authenticated'], 401);
        }
        $tokenHelper = new TokenHelper();
        $token = $_REQUEST['token'];
        $user = $tokenHelper->isTokenValid($token, $this->nacho->getUserHandler()->getUsers());
        if (!$user) {
            return $this->json(['message' => 'The provided Token is invalid'], 401);
        }
        if (!key_exists('entry', $_GET)) {
            return $this->json($_GET, 400);
        }

        $entry = $_GET['entry'];

        $success = $this->contentHelper->delete($entry);

        if ($success) {
            return $this->json(['message' => "successfully deleted ${entry}"]);
        } else {
            return $this->json(['message' => "error deleting ${entry}"], 404);
        }
    }

    public function buildCache()
    {
        if (!key_exists('token', $_REQUEST)) {
            return $this->json(['message' => 'You need to be authenticated'], 401);
        }
        $tokenHelper = new TokenHelper();
        $token = $_REQUEST['token'];
        $user = $tokenHelper->isTokenValid($token, $this->nacho->getUserHandler()->getUsers());
        if (!$user) {
            return $this->json(['message' => 'The provided Token is invalid'], 401);
        }
        $cacheHelper = new CacheHelper($this->nacho);
        $cacheHelper->build();
        return $this->json(['success' => true]);
    }

    public function generateBackup()
    {
        if (!key_exists('token', $_REQUEST)) {
            return $this->json(['message' => 'You need to be authenticated'], 401);
        }
        $tokenHelper = new TokenHelper();
        $token = $_REQUEST['token'];
        $user = $tokenHelper->isTokenValid($token, $this->nacho->getUserHandler()->getUsers());
        if (!$user) {
            return $this->json(['message' => 'The provided Token is invalid'], 401);
        }

        $backupHelper = new BackupHelper();
        $zip = $backupHelper->generateBackup();

        return $this->json(['file' => $zip]);
    }

    public function restoreFromBackup(Request $request)
    {
        if (!key_exists('token', $request->getBody())) {
            return $this->json(['message' => 'You need to be authenticated'], 401);
        }
        $tokenHelper = new TokenHelper();
        $token = $request->getBody()['token'];
        $user = $tokenHelper->isTokenValid($token, $this->nacho->getUserHandler()->getUsers());
        if (!$user) {
            return $this->json(['message' => 'The provided Token is invalid'], 401);
        }

        $zipPath = $_FILES['backup']['tmp_name'];
        $backupHelper = new BackupHelper();
        $success = $backupHelper->restoreFromBackup($zipPath);
        $cacheHelper = new CacheHelper($this->nacho);
        $cacheHelper->build();

        return $this->json(['success' => $success]);
    }
}
