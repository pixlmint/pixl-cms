<?php

namespace PixlMint\CMS\Contracts;

use Nacho\Contracts\RequestInterface;
use Nacho\Models\HttpResponse;
use PixlMint\CMS\Helpers\Admin\BaseEntryHandler;
use PixlMint\CMS\Helpers\Admin\EditContentHandler;
use PixlMint\CMS\Helpers\Admin\PageSecurityHandler;

interface AdminControllerInterface
{
    public function edit(EditContentHandler $editContentHandler): HttpResponse;

    public function changePageSecurity(PageSecurityHandler $pageSecurityHandler): HttpResponse;

    public function addFolder(BaseEntryHandler $entryHandler): HttpResponse;

    public function deleteFolder(BaseEntryHandler $entryHandler, RequestInterface $request): HttpResponse;

    public function add(BaseEntryHandler $entryHandler): HttpResponse;

    public function rename(BaseEntryHandler $entryHandler, RequestInterface $request): HttpResponse;
}

