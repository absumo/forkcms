<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\Command;

use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolderRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DeleteMediaFolderHandler
{
    public function __construct(protected MediaFolderRepository $mediaFolderRepository)
    {
    }

    public function __invoke(DeleteMediaFolder $deleteMediaFolder): void
    {
        $mediaFolder = $deleteMediaFolder->mediaFolder;

        $this->mediaFolderRepository->remove($mediaFolder);
    }
}
