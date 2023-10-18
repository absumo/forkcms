<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\Command;

use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;

final class DeleteMediaFolder
{
    public function __construct(public MediaFolder $mediaFolder)
    {
    }
}
