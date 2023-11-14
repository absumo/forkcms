<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Command;

use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItem;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItemDataTransferObject;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

final class UpdateMediaItem extends MediaItemDataTransferObject
{
    public ?UploadedFile $file = null;

    #[Vich\UploadableField(mapping: 'files', fileNameProperty: 'fileName')]
    public ?File $vich = null;

    public function __construct(MediaItem $mediaItem)
    {
        parent::__construct($mediaItem);
    }

    public static function fromCrop(CropMediaItem $data): self
    {
        $self = new self($data->mediaItemEntity);
        $self->file = $data->file;
        $self->vich = $data->crop;

        return $self;
    }
}
