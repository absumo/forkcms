<?php

namespace ForkCMS\Modules\MediaLibrary\Backend\Actions;

use ForkCMS\Modules\Backend\Domain\Action\AbstractDataGridActionController;
use ForkCMS\Modules\Backend\Domain\Action\ActionServices;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolderRepository;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItem;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\NavigationBuilder;
use Symfony\Component\HttpFoundation\Request;

class MediaItemIndex extends AbstractDataGridActionController
{
    public function __construct(
        ActionServices $actionServices,
        private readonly NavigationBuilder $navigationBuilder,
        private readonly MediaFolderRepository $mediaFolderRepository,
    ) {
        parent::__construct($actionServices);
    }

    protected function execute(Request $request): void
    {
        $this->assign('sidebarTree', $this->navigationBuilder->getTree(Locale::current()));

        // TODO: MediaFolder filter
        $mediaFolder = $this->mediaFolderRepository->find($request->query->getInt('folder'));
        $searchQuery = $request->get('query');

        $dataGrid = $this->dataGridFactory->forEntity(MediaItem::class);

        $this->assign('dataGrid', $dataGrid);
    }
}
