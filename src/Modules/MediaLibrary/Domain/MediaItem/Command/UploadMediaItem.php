<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Command;

use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
final class UploadMediaItem
{
    private ?MediaItem $mediaItem = null;

    public ?MediaFolder $mediaFolder = null;

    public string $title;

    #[Assert\File(mimeTypes: ['image/png'])]
    public ?UploadedFile $file = null;

    public $fileName;

    public $size;

    #[Vich\UploadableField(mapping: 'files', fileNameProperty: 'fileName')]
    // #[Assert\File(maxSize: '20M')]
    public ?File $vich = null;

    public function __construct()
    {
    }

    public function getMediaItem(): MediaItem
    {
        return $this->mediaItem;
    }

    public function setMediaItem(MediaItem $mediaItem): void
    {
        $this->mediaItem = $mediaItem;
    }
}
