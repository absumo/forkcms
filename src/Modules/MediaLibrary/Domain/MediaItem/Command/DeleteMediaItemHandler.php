<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Command;

use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItemRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DeleteMediaItemHandler
{
    public function __construct(private readonly MediaItemRepository $mediaItemRepository)
    {
    }

    public function __invoke(DeleteMediaItem $deleteMediaItem): void
    {
        $mediaItem = $this->mediaItemRepository->find($deleteMediaItem->mediaItem);
        if ($mediaItem) {
            $this->mediaItemRepository->remove($mediaItem);
        }
    }
}
