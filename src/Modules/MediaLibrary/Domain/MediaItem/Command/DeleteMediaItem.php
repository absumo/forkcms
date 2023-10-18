<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Command;

final class DeleteMediaItem
{
    public function __construct(public string $mediaItem)
    {
    }
}
