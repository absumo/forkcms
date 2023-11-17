<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\Command;

use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolderDataTransferObject;

final class CreateMediaFolder extends MediaFolderDataTransferObject
{
    public function __construct(?MediaFolder $parent = null)
    {
        parent::__construct();

        $this->parent = $parent;
    }
}
