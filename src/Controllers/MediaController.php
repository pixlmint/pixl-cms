<?php

namespace PixlMint\CMS\Controllers;

use Nacho\Models\Request;
use PixlMint\CMS\Contracts\MediaProcessor;
use PixlMint\CMS\Helpers\CMSConfiguration;
use PixlMint\CMS\Helpers\CustomUserHelper;
use PixlMint\CMS\Helpers\Media\EntryMediaLoader;
use PixlMint\CMS\Helpers\Media\ImageMediaType;
use PixlMint\CMS\Helpers\Media\MediaFactory;
use PixlMint\CMS\Helpers\Media\MimeHelper;
use PixlMint\CMS\Helpers\Media\VideoMediaType;
use PixlMint\CMS\Models\MediaDirectory;
use PixlMint\CMS\Models\Mime;
use Nacho\Controllers\AbstractController;
use Nacho\Nacho;

class MediaController extends AbstractController
{
    /** @var array|MediaProcessor[] $mediaHelpers */
    private array $mediaHelpers = [];

    public function __construct(Nacho $nacho)
    {
        parent::__construct($nacho);
        $this->mediaHelpers['img'] = new ImageMediaType();
        $this->mediaHelpers['vid'] = new VideoMediaType();
    }

    /**
     * GET: /api/admin/entry/gallery/upload
     */
    public function uploadMedia(): string
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }
        if (!key_exists('entry', $_REQUEST)) {
            return $this->json(['message' => 'Please define the Entry'], 400);
        }

        $mediaDir = CMSConfiguration::mediaDir();
        $entry = $_REQUEST['entry'];
        $month = explode('/', $entry)[1];
        $day = explode('/', $entry)[2];
        $mediaDirectory = new MediaDirectory($month, $day);

        if (!is_dir("${mediaDir}/${entry}")) {
            mkdir("${mediaDir}${entry}", 0777, true);
        }

        $uploadedFiles = [];

        foreach ($_FILES as $file) {
            $helper = $this->getMediaHelper(Mime::init($file['type']));
            $media = $helper->storeMedia($file, $mediaDirectory);
            $tmpArr = $media->toFrontendArray();
            $tmpArr['scaled']['default'] = $media->getMediaPath($helper->getDefaultScaled());
            $uploadedFiles[] = $tmpArr;
        }

        return $this->json(['message' => 'uploaded files', 'files' => $uploadedFiles]);
    }

    // /api/admin/entry/media/load
    public function loadMediaForEntry(): string
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }

        $media = [];
        foreach ($this->mediaHelpers as $slug => $helper) {
            $media[] = [
                'name' => $helper::getName(),
                'slug' => $slug,
                'media' => EntryMediaLoader::run($_REQUEST['entry'], $helper),
            ];
        }

        return $this->json(["media" => $media]);
    }

    // /api/admin/entry/media/delete
    public function deleteMedia(Request $request): string
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }

        $img = $request->getBody()['media'];

        $media = MediaFactory::run($img, $this->mediaHelpers);
        $delete = [];
        foreach ($this->mediaHelpers as $helper) {
            $delete[] = $helper->deleteMedia($media);
        }

        return $this->json($delete);
    }

    private function getMediaHelper(Mime $mime): MediaProcessor
    {
        foreach ($this->mediaHelpers as $mediaHelper) {
            $testMime = Mime::init($mediaHelper::getMimeType());
            if (MimeHelper::compareMimeTypes($testMime, $mime)) {
                return $mediaHelper;
            }
        }

        throw new \Exception('The Mime Type ' . $mime->printMime() . ' is not supported');
    }
}
