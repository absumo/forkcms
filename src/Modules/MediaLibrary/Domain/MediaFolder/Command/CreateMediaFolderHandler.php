<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\Command;

use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolderRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateMediaFolderHandler
{
    public function __construct(protected MediaFolderRepository $mediaFolderRepository)
    {
    }

    public function __invoke(CreateMediaFolder $createMediaFolder): void
    {
        $mediaFolder = MediaFolder::fromDataTransferObject($createMediaFolder);
        $this->mediaFolderRepository->add($mediaFolder);

        // We redefine the MediaFolder, so we can use it in an action
        $createMediaFolder->setMediaFolderEntity($mediaFolder);
    }
}
