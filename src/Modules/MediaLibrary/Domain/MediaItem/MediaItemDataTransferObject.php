<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaItem;

use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;
use Symfony\Component\Validator\Constraints as Assert;

class MediaItemDataTransferObject
{
    public ?MediaFolder $folder;

    #[Assert\NotBlank(message: 'err.FieldIsRequired')]
    public string $title;

    public string $path;

    public int $userId;

    public function __construct(protected ?MediaItem $mediaItemEntity = null)
    {
        if (!$this->hasExistingMediaItem()) {
            return;
        }

        $this->folder = $this->mediaItemEntity->getFolder();
        $this->path = $this->mediaItemEntity->getPath();
        $this->title = $this->mediaItemEntity->getTitle();
        $this->userId = $this->mediaItemEntity->getUserId();
    }

    public function getMediaItemEntity(): MediaItem
    {
        return $this->mediaItemEntity;
    }

    public function hasExistingMediaItem(): bool
    {
        return $this->mediaItemEntity instanceof MediaItem;
    }

    public function setMediaItemEntity(MediaItem $mediaItemEntity): void
    {
        $this->mediaItemEntity = $mediaItemEntity;
    }
}
