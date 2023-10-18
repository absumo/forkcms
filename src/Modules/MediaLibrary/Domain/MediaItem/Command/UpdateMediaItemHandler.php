<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItem;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItemRepository;

final class UpdateMediaItemHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly MediaItemRepository $mediaItemRepository,
    ) {
    }

    public function __invoke(UpdateMediaItem $updateMediaItem): void
    {
        $mediaItem = MediaItem::fromDataTransferObject($updateMediaItem);
        if ($mediaItem) {
            $this->mediaItemRepository->save($mediaItem);
        }
    }
}
