<?php

namespace ForkCMS\Modules\MediaLibrary\Backend\Actions;

use ForkCMS\Modules\Backend\Domain\Action\AbstractActionController;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MediaItemShow extends AbstractActionController
{
    protected MediaItem $mediaItem;

    protected function execute(Request $request): void
    {
        $this->mediaItem = $this->getEntityFromRequest($request, MediaItem::class);
    }

    public function getResponse(Request $request): Response
    {
        return new RedirectResponse($this->mediaItem->webpath);
    }
}
