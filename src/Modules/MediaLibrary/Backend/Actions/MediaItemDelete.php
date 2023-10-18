<?php

namespace ForkCMS\Modules\MediaLibrary\Backend\Actions;

use ForkCMS\Modules\Backend\Domain\Action\AbstractDeleteActionController;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Command\DeleteMediaItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MediaItemDelete extends AbstractDeleteActionController
{
    protected function getFormResponse(Request $request): ?Response
    {
        return $this->handleDeleteForm(
            $request,
            DeleteMediaItem::class,
            MediaItemIndex::getActionSlug()
        );
    }
}
