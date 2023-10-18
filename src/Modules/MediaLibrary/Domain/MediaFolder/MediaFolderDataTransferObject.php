<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaFolder;

class MediaFolderDataTransferObject
{
    public ?MediaFolder $parent;

    public string $name;

    public int $userId;

    public function __construct(protected ?MediaFolder $mediaFolderEntity = null)
    {
        if (!$this->hasExistingMediaFolder()) {
            return;
        }

        $this->name = $this->mediaFolderEntity->getName();
        $this->parent = $this->mediaFolderEntity->getParent();
        $this->userId = $this->mediaFolderEntity->getUserId();
    }

    public function getMediaFolderEntity(): MediaFolder
    {
        return $this->mediaFolderEntity;
    }

    public function hasExistingMediaFolder(): bool
    {
        return $this->mediaFolderEntity instanceof MediaFolder;
    }

    public function setMediaFolderEntity(MediaFolder $mediaFolderEntity): void
    {
        $this->mediaFolderEntity = $mediaFolderEntity;
    }
}
