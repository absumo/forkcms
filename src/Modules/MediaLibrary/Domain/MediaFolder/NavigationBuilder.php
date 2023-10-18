<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaFolder;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleSettings;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\Pages\Domain\Page\Page;
use ForkCMS\Modules\Pages\Domain\Revision\MenuType;
use ForkCMS\Modules\Pages\Domain\Revision\Revision;
use Psr\Cache\CacheItemPoolInterface;

// /** @phpstan-type PageCache array{attr: array<string, string>, page: Page, children: array{attr: array<string, string>, page: Page, children: array{attr: array<string, string>, page: Page, children: array{attr: array<string, string>, page: Page, children: mixed[]|null}[]|null}[]|null}[]|null} */
final class NavigationBuilder
{
    public const GROUPED_MEDIA_LIBRARY_CACHE_KEY = 'media_library_grouped_';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheItemPoolInterface $cache,
        private readonly ModuleSettings $moduleSettings,
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
        /** @var MediaFolder[] $folders */
        $folders = $this->entityManager->createQueryBuilder()
            ->select('mf')
            ->from(MediaFolder::class, 'mf')
            ->where('mf.parent IS NULL')
            ->getQuery()
            ->getResult();

        $tree = [];

        // TODO: See TreeManager

        foreach ($folders as $folder) {
            $tree[$folder->getId()] = [
                'label' => $folder->getName(),
                'children' => self::getSubTree2($folder),
            ];
        }

        static $cache;
        if ($cache === null) {
            $cache = [];
        }
        if (array_key_exists($locale->value, $cache)) {
            return $cache[$locale->value];
        }

        $tree = [];
        $groupedPages = $this->getGroupedPages($locale);
        foreach (MenuType::cases() as $type) {
            if (
                $type === MenuType::META
                && !$this->moduleSettings->get(ModuleName::fromString('MediaLibrary'), 'meta_navigation', false)
            ) {
                continue;
            }

            $tree[$type->value] = [
                'label' => $type,
                'pages' => self::getSubTree($type, $groupedPages, $locale),
            ];
        }
        $cache[$locale->value] = $tree;

        return $tree;
    }

    /** @return array<int, Page> */
    public function getActivePages(Revision $revision): array
    {
        static $cache;
        $activeId = $revision->getPage()->getId();
        if ($cache === null) {
            $cache = [];
        }
        if (array_key_exists($activeId, $cache)) {
            return $cache[$activeId];
        }

        $tree = $this->getTree($revision->getLocale())[$revision->getType()->value]['pages'] ?? [];
        $pages = new ArrayCollection();
        foreach ($tree as $page) {
            if ($this->findActivePages($page, $activeId, $pages)) {
                $pages->set($page['page']->getId(), $page['page']);

                break;
            }
        }
        $cache[$activeId] = $pages->toArray();

        return $cache[$activeId];
    }

    /** @return array<int, array<int, array{page: Page, active: bool, hasChildren: bool}>> */
    public function getActiveGroupedPages(MenuType $type, Revision $revision): array
    {
        static $cache;
        if ($cache === null) {
            $cache = [];
        }
        $cacheKey = $type->value . '_' . $revision->getId();
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $pages = $this->getGroupedPages($revision->getLocale())[$type->value] ?? [];
        $activeIds = $this->getActivePages($revision);
        if (count($activeIds) > 1 && $revision->getPage()->getId() !== Page::PAGE_ID_HOME) {
            unset($activeIds[Page::PAGE_ID_HOME]);
        }

        foreach ($pages as $parentId => $childPages) {
            $pages[$parentId] = array_map(
                static fn (Page $page): array => [
                    'page' => $page,
                    'active' => array_key_exists($page->getId(), $activeIds),
                    'hasChildren' => array_key_exists($page->getId(), $pages) && $page->getId() !== Page::PAGE_ID_HOME,
                ],
                $childPages
            );
        }
        $cache[$cacheKey] = $pages;

        return $pages;
    }

    /** @return array<value-of<MenuType>, array<int, array<int, Page>>> */
    private function getGroupedPages(Locale $locale): array
    {
        $cache = $this->cache->getItem(self::GROUPED_MEDIA_LIBRARY_CACHE_KEY . $locale->value);
        if ($cache->isHit()) {
            return $cache->get();
        }

        /** @var Page[] $pages */
        $pages = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from(Page::class, 'p')
            ->setParameter('locale', $locale->value)
            ->innerJoin(
                'p.revisions',
                'pr',
                Join::WITH,
                'pr.locale = :locale AND pr.isArchived IS NULL'
            )
            ->addSelect('pr')
            ->leftJoin('pr.blocks', 'prb')
            ->addSelect('prb')
            ->leftJoin('pr.meta', 'prm')
            ->addSelect('prm')
            ->leftJoin(
                'p.childRevisions',
                'cr',
                Join::WITH,
                'cr.locale = :locale AND cr.isArchived IS NULL'
            )
            ->addSelect('cr')
            ->leftJoin('pr.page', 'crp')
            ->addSelect('crp')
            ->getQuery()
            ->getResult();

        $groupedPages = [];
        foreach ($pages as $page) {
            $revision = $page->getActiveRevision($locale);
            $type = $revision->getType()->value;
            $pageId = $page->getId();
            $groupedPages[$type][$revision->getParentPage()?->getId() ?? 0][$pageId] = $page;
        }

        $cache->set($groupedPages);
        $this->cache->save($cache);

        return $groupedPages;
    }

    /**
     * @param PageCache $page
     * @param ArrayCollection<int, Page> $pages
     */
    private function findActivePages(array $page, int $activeId, ArrayCollection $pages): bool
    {
        if ($page['page']->getId() === $activeId) {
            $pages->set($page['page']->getId(), $page['page']);

            return true;
        }

        foreach ($page['children'] ?? [] as $id => $childPage) {
            if ($id === $activeId) {
                $pages->set($childPage['page']->getId(), $childPage['page']);

                return true;
            }

            if ($this->findActivePages($childPage, $activeId, $pages)) {
                $pages->set($childPage['page']->getId(), $childPage['page']);

                return true;
            }
        }

        return false;
    }

    /**
     * @param array<value-of<MenuType>, array<int, array<int, Page>>> $groupedPages
     *
     * @return ?array<int, PageCache>
     */
    private static function getSubTree(MenuType $type, array $groupedPages, Locale $locale, int $parentId = 0): ?array
    {
        /** @var Page[]|null $subPages */
        $subPages = $groupedPages[$type->value][$parentId] ?? null;

        if ($subPages === null || count($subPages) === 0) {
            return null;
        }

        $subTree = [];
        foreach ($subPages as $page) {
            $pageTreeType = $page->getPageTreeType($locale);
            $pageId = $page->getId();
            $subTree[$pageId] = [
                'attr' => [
                    'rel' => $pageTreeType,
                    'data-jstree' => '{"type":"' . $pageTreeType . '"}',
                ],
                'page' => $page,
                'children' => self::getSubtree($type, $groupedPages, $locale, $pageId),
            ];
        }

        return $subTree;
    }

    private static function getSubTree2(MediaFolder $parentFolder): array
    {
        $subTree = [];
        /** @var MediaFolder $folder */
        foreach ($parentFolder->getChildren() as $folder) {
            $subTree[$folder->getId()] = [
                'label' => $folder->getName(),
                'children' => self::getSubTree2($folder)
            ];
        }

        return $subTree;
    }
}
