<?php

namespace ForkCMS\Modules\MediaLibrary\Backend\Actions;

use ForkCMS\Modules\Backend\Domain\Action\AbstractActionController;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MediaItemFindAll extends AbstractActionController
{
    /** @var array<string, mixed> */
    private array $data = [];

    public function execute(Request $request): void
    {
        $folders = $this->getRepository(MediaFolder::class)->findTopLevel();
        $this->data = $folders;
    }

    public function getResponse(Request $request): Response
    {
        return new JsonResponse($this->data);
    }
}
