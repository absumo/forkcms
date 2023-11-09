<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\Twig;

use Pageon\DoctrineDataGridBundle\DataGrid\DataGrid;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class DataGridExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'media_library_datagrid',
                [$this, 'parseDataGrid'],
                [
                    'needs_environment' => true,
                    'is_safe' => ['html']
                ]
            )
        ];
    }

    public function parseDataGrid(
        Environment $twig,
        DataGrid $dataGrid,
        array $parameters = [],
        string $template = '@MediaLibrary/dataGrid.html.twig'
    ): string {
        return $twig->render($template, ['dataGrid' => $dataGrid] + $parameters);
    }
}