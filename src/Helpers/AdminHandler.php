<?php

namespace PixlMint\CMS\Helpers;

use Nacho\Contracts\RequestInterface;
use Nacho\Contracts\UserHandlerInterface;
use Nacho\Helpers\PageManager;
use Nacho\Helpers\PicoVersioningHelper;
use Nacho\Models\PicoPage;
use Nacho\Models\Request;
use DateTime;
use Nacho\Contracts\PageManagerInterface;
use Nacho\Helpers\HookHandler;
use Nacho\Helpers\Utils;
use Nacho\Hooks\NachoAnchors\PostHandleUpdateAnchor;
use Nacho\Models\HttpResponse;
use Nacho\Nacho;
use PixlMint\CMS\Actions\RenameAction;
use PixlMint\CMS\Helpers\CustomUserHelper;
use PixlMint\CMS\Helpers\BackupHelper;
use Nacho\Controllers\AbstractController;
use Nacho\Exceptions\BadRequestHttpException;
use Nacho\Exceptions\ConflictHttpException;
use Nacho\Exceptions\NotFoundHttpException;
use Nacho\Exceptions\UnauthorizedHttpException;
use Nacho\Models\HttpMethod;
use Nacho\Models\HttpResponseCode;
use PixlMint\CMS\Helpers\TokenHelper;
use PixlMint\JournalPlugin\Helpers\CacheHelper;

/**
 * Responsible for handling most content operations
 * Used for example by {@see PixlMint\CMS\Controllers\AdminController}
 */
class AdminContentHandler
{
    private PicoVersioningHelper $versioningHelper;
    private PageManager $pageManager;
    private UserHandlerInterface $userHandler;

    public function __construct(PicoVersioningHelper $versioningHelper, PageManager $pageManager, UserHandlerInterface $userHandler)
    {
        $this->versioningHelper = $versioningHelper;
        $this->pageManager = $pageManager;
        $this->userHandler = $userHandler;
    }

    public function handleEdit(RequestInterface $request): PicoPage
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            throw new UnauthorizedHttpException();
        }
        if (!$request->getBody()->has('entry')) {
            throw new BadRequestHttpException('Please define the entry');
        }
        if ($request->isMethod(HttpMethod::PUT) && (!$request->getBody()->has('lastUpdate') || !$request->getBody()->has('content'))) {
            throw new BadRequestHttpException('Please define content and lastUpdate arguments');
        }
        $meta = $request->getBody()->get('meta');
        if (Utils::isJson($meta)) {
            $request->getBody()->set('meta', json_decode($meta, TRUE));
        }
        $strPage = $request->getBody()->get('entry');
        $page = $this->pageManager->getPage($strPage);

        if (!$page || !is_file($page->file)) {
            throw new NotFoundHttpException('Unable to find this file');
        }

        if (strtoupper($request->requestMethod) === HttpMethod::PUT) {
            $this->saveEditedContent($request, $page);
        }

        return $page;
    }

    private function saveEditedContent(RequestInterface $request, PicoPage $page)
    {
        $meta = [];
        if ($request->getBody()->has('meta')) {
            $meta = $request->getBody()->get('meta');
            if (Utils::isJson($meta)) {
                $meta = json_decode($meta, TRUE);
            }
        }
        $lastUpdateTime = $page->meta->dateUpdated;
        if (is_numeric($lastUpdateTime)) {
            $lastUpdateTime = date('Y-m-d H:i:s', $lastUpdateTime);
        }
        if (!$this->versioningHelper->hasValidUpdateTime($request)) {
            throw new BadRequestHttpException('Invalid lastUpdate date supplied: ' . $request->getBody()->get('lastUpdate'));
        }
        if (!$this->versioningHelper->canUpdateToVersion($page, $request->getBody()->get('lastUpdate'))) {
            throw new ConflictHttpException("This page has already been updated by another client more recently");
        }
        $content = $request->getBody()->get('content');
        $this->pageManager->editPage($page->id, $content, $meta);
    }

    private function isGranted($role): bool
    {
        return $this->userHandler->isGranted($role);
    }
}

