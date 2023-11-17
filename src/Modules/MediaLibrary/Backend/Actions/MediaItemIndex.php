<?php

namespace ForkCMS\Modules\MediaLibrary\Backend\Actions;

use ForkCMS\Core\Domain\Form\ActionType;
use ForkCMS\Modules\Backend\Domain\Action\AbstractDataGridActionController;
use ForkCMS\Modules\Backend\Domain\Action\ActionServices;
use ForkCMS\Modules\Backend\Domain\Action\ActionSlug;
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

        $this->addDeleteForm(
            ['id' => null],
            ActionSlug::fromFQCN(MediaItemDelete::class)
        );
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     */
    final protected function addDeleteForm(
        array $data,
        ActionSlug $deleteActionSlug,
        string $formType = ActionType::class,
        array $options = []
    ): void {
        $this->assign('crudDeleteAction', $deleteActionSlug->getActionName());
        $this->assign(
            'backend_delete_form',
            $this->formFactory->create(
                $formType,
                $data,
                array_merge(['actionSlug' => $deleteActionSlug], $options)
            )->createView()
        );
    }
}
