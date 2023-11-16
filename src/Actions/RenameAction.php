<?php

namespace PixlMint\CMS\Actions;

use Nacho\Contracts\PageManagerInterface;
use Nacho\Nacho;
use PixlMint\CMS\Contracts\ActionInterface;

class RenameAction implements ActionInterface
{
    public static function run(array $arguments): bool
    {
        $pageManager = Nacho::$container->get(PageManagerInterface::class);
        $newName = $arguments['new-title'];
        $entry = $arguments['entry'];

        $pageManager->editPage($entry, '', ['title' => $newName]);
        $page = $pageManager->getPage($entry);
        $splPath = explode(DIRECTORY_SEPARATOR, $page->file);
        $filename = array_pop($splPath);

        if ($filename === 'index.md') {
            return true;
        }

        return true;
    }
}