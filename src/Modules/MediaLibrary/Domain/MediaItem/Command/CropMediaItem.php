<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Command;

use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItem;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItemDataTransferObject;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
final class CropMediaItem extends MediaItemDataTransferObject
{
    public function __construct(MediaItem $mediaItem)
    {
        parent::__construct($mediaItem);
    }

    public ?UploadedFile $file = null;

    public string $fileName;

    #[Vich\UploadableField(mapping: 'files', fileNameProperty: 'fileName')]
    public ?File $crop = null;
}
