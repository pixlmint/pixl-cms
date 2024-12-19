<?php

namespace PixlMint\CMS\Helpers;

use Nacho\Contracts\PageManagerInterface;
use Nacho\Helpers\PageManager;
use Nacho\Helpers\PageSecurityHelper;
use PixlMint\CMS\Models\NavCache;
use PixlMint\CMS\Repository\NavCacheRepository;
use Psr\Log\LoggerInterface;

class NavRenderer
{
    private PageManagerInterface $pageManager;
    private NavCacheRepository $navCacheRepository;
    private LoggerInterface $logger;

    public function __construct(PageManagerInterface $pageManager, NavCacheRepository $navCacheRepository, LoggerInterface $logger)
    {
        $this->pageManager = $pageManager;
        $this->navCacheRepository = $navCacheRepository;
        $this->logger = $logger;
    }

    /**
     * Return an array based on a nested pages array.
     */
    public function loadNav(?array $pages = [], bool $rerender = false): array
    {
        if ($rerender) {
            $this->logger->info('Rerendering nav');
            if (!$pages) {
                $originalPageTreeSetting = PageManager::$INCLUDE_PAGE_TREE;
                PageManager::$INCLUDE_PAGE_TREE = true;
                $this->pageManager->readPages();
                $pages = $this->pageManager->getPageTree();
                PageManager::$INCLUDE_PAGE_TREE = $originalPageTreeSetting;
            }
            $loadedNav = $this->loadNavFromPages($pages);
            $this->cacheLoadedNav($loadedNav);
            return $loadedNav;
        } else {
            $cachedNav = $this->getNavCache();
            if (!$cachedNav->getNav()) {
                $this->logger->info('Nav is empty, rerendering');
                return $this->loadNav([], true);
            } else {
                $this->logger->info('Found cached nav');
                return $cachedNav->getNav();
            }
        }
    }

    private function loadNavFromPages(array $pages): array
    {
        $ret = [];
        foreach ($pages as $pageID => $page) {
            if (!empty($page->hidden)) continue;

            $childrenOutput = [];
            if (isset($page->children)) {
                $childrenOutput = $this->loadNav($page->children, true);
            }

            $url = $page->url ?? false;
            $title = $page->meta->title;
            $isPublic = PageSecurityHelper::isPagePublic($page);

            $ret[] = [
                'id' => $page->id,
                'title' => $title,
                'url' => $url,
                'children' => $childrenOutput,
                'isFolder' => str_ends_with($page->file, 'index.md'),
                'isPublic' => $isPublic,
                'kind' => $page->meta->kind ?? 'plain',
            ];
        }
        return $ret;
    }

    private function getNavCache(): NavCache
    {
        $navCache = $this->navCacheRepository->getById(1);
        if (!($navCache instanceof NavCache)) {
            $navCache = new NavCache([]);
            $navCache->setId(1);
            $this->navCacheRepository->set($navCache);
        }
        return $navCache;
    }

    private function cacheLoadedNav(array $nav): void
    {
        $navCache = $this->getNavCache();
        $navCache->setNav($nav);
        $this->navCacheRepository->set($navCache);
    }
}
