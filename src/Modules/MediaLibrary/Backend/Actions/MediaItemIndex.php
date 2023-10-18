<?php

namespace ForkCMS\Modules\MediaLibrary\Backend\Actions;

use Doctrine\ORM\QueryBuilder;
use ForkCMS\Modules\Backend\Domain\Action\AbstractDataGridActionController;
use ForkCMS\Modules\Backend\Domain\Action\ActionServices;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolderRepository;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItem;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Type;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\NavigationBuilder;
use Pageon\DoctrineDataGridBundle\DataGrid\DataGrid;
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

        $mediaFolder = $this->mediaFolderRepository->find($request->query->getInt('folder'));
        $searchQuery = $request->get('query');

        $dataGrids = [];
        foreach (Type::cases() as $type) {
            $dataGrids[$type->value] = $this->getDataGrid($type, $mediaFolder, $searchQuery);
        }
        // $dataGrids = [$this->getDataGrid(null, $mediaFolder, $searchQuery)];

        $this->assign('dataGrids', $dataGrids);
        $this->assign('hasResults', $this->hasResults($dataGrids));
    }

    protected function getDataGrid(?Type $type, ?MediaFolder $mediaFolder, ?string $searchQuery): DataGrid
    {
        return $this->dataGridFactory->forEntity(
            MediaItem::class,
            fn (QueryBuilder $qb) => $qb->andWhere('MediaItem.type = :type')->setParameter('type', $type),
            10
        );
    }

    private function hasResults(array $dataGrids): bool
    {
        $totalResultCount = array_sum(
            array_map(
                static fn (DataGrid $dataGrid) => $dataGrid->getPaginator()->getTotalItemCount(),
                $dataGrids
            )
        );

        return $totalResultCount > 0;
    }
}
