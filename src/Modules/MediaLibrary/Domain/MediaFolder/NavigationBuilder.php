<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaFolder;

use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Pages\Domain\Revision\MenuType;
use Psr\Cache\CacheItemPoolInterface;

/** @phpstan-type PageCache array{attr: array<string, string>, page: Page, children: array{attr: array<string, string>, page: Page, children: array{attr: array<string, string>, page: Page, children: array{attr: array<string, string>, page: Page, children: mixed[]|null}[]|null}[]|null}[]|null} */
final class NavigationBuilder
{
    public const GROUPED_MEDIA_LIBRARY_CACHE_KEY = 'media_library_grouped_';

    public function __construct(
        private readonly MediaFolderRepository $mediaFolderRepository,
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    public function clearNavigationCache(): void
    {
        $this->cache->deleteItems(
            array_map(
                static fn (Locale $locale): string => self::GROUPED_MEDIA_LIBRARY_CACHE_KEY . $locale->value,
                Locale::cases()
            )
        );
    }

    /** @return array<value-of<MenuType>, array{label:MenuType, pages: PageCache[]}> */
    public function getTree(Locale $locale): array
    {
        static $cache;
        if ($cache === null) {
            $cache = [];
        }
        if (array_key_exists($locale->value, $cache)) {
            return $cache[$locale->value];
        }

        $folders = $this->mediaFolderRepository->findTopLevel();

        $tree = self::getSubTree($folders);

        $cache[$locale->value] = $tree;

        return $tree;
    }

    /** @param iterable<MediaFolder> $folders */
    private static function getSubTree(iterable $folders): array
    {
        $subTree = [];
        foreach ($folders as $folder) {
            $subTree[$folder->getId()] = [
                'label' => $folder->getName(),
                'itemCount' => $folder->getItems()->count(),
                'children' => self::getSubTree($folder->getChildren())
            ];
        }

        return $subTree;
    }
}
