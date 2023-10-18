<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\Command;

use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolderDataTransferObject;

final class UpdateMediaFolder extends MediaFolderDataTransferObject
{
    public function __construct(MediaFolder $mediaFolder)
    {
        parent::__construct($mediaFolder);
    }
}
