<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaItem;

enum StorageType: string
{
    case EXTERNAL = 'external';
    case LOCAL = 'local';
    case YOUTUBE = 'youtube';
    case VIMEO = 'vimeo';
}
