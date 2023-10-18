<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Command;

use ForkCMS\Core\Domain\MessageHandler\CommandHandlerInterface;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItem;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItemRepository;

final class UploadMediaItemHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly MediaItemRepository $mediaItemRepository,
    ) {
    }

    public function __invoke(UploadMediaItem $uploadMediaItem): void
    {
        $m = MediaItem::createFromFile($uploadMediaItem->vich, $uploadMediaItem->mediaFolder, 1);

        // $m = MediaItem::fromDataTransferObject($uploadMediaItem);
        $this->mediaItemRepository->save($m);
    }
}
