<?php

namespace PixlMint\CMS\Actions;

use Nacho\Helpers\PageManager;
use PixlMint\CMS\Contracts\ActionInterface;

class RenameAction implements ActionInterface
{
    private static PageManager $pageManager;

    public static function setPageManager(PageManager $pageManager): void
    {
        self::$pageManager = $pageManager;
    }

    public static function run(array $arguments): bool
    {
        $newName = $arguments['new-title'];
        $entry = $arguments['entry'];

        self::$pageManager->editPage($entry, '', ['title' => $newName]);
        $page = self::$pageManager->getPage($entry);
        $splPath = explode(DIRECTORY_SEPARATOR, $page->file);
        $filename = array_pop($splPath);

        if ($filename === 'index.md') {
            return true;
        }

        return true;
    }
}