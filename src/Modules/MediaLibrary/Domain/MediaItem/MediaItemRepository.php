<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaItem;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Exception\MediaItemNotFound;

/**
 * @method MediaItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method MediaItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method MediaItem|null findOneByUrl(string $url)
 * @method MediaItem[]    findAll()
 * @method MediaItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends ServiceEntityRepository<MediaItem>
 */
final class MediaItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, MediaItem::class);
    }

    public function save(MediaItem $mediaItem): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($mediaItem);
        $entityManager->flush();
    }

    public function remove(MediaItem $mediaItem): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($mediaItem);
        $entityManager->flush();
    }

    public function existsOneByUrl(string $url): bool
    {
        /** @var MediaItem|null $mediaItem */
        $mediaItem = $this->findOneByUrl($url);

        return $mediaItem instanceof MediaItem;
    }

    public function findOneById(string $id = null): MediaItem
    {
        if ($id === null) {
            throw MediaItemNotFound::forEmptyId();
        }

        $mediaItem = parent::find($id);

        if (!$mediaItem instanceof MediaItem) {
            throw MediaItemNotFound::forId($id);
        }

        return $mediaItem;
    }

    public function findByFolderAndAspectRatio(MediaFolder $mediaFolder, ?AspectRatio $aspectRatio): array
    {
        $condition = ['folder' => $mediaFolder];

        if ($aspectRatio instanceof AspectRatio) {
            $condition['aspectRatio'] = $aspectRatio;
        }

        return $this->findBy(
            $condition,
            ['title' => 'ASC']
        );
    }

    public function findByFolderAndAspectRatioAndSearchQuery(
        MediaFolder $mediaFolder,
        ?AspectRatio $aspectRatio,
        ?string $searchQuery
    ): array {
        $queryBuilder = $this->createQueryBuilder('i')
            ->select('i');

        $queryBuilder = $queryBuilder->where('i.folder = :folder')
            ->orderBy('i.title', 'ASC')
            ->setParameter('folder', $mediaFolder);

        if ($aspectRatio instanceof AspectRatio) {
            $queryBuilder = $queryBuilder->andWhere('i.aspectRatio = :aspectRatio')
                ->setParameter('aspectRatio', $aspectRatio);
        }

        if ($searchQuery) {
            $queryBuilder = $queryBuilder->andWhere('i.title LIKE :searchQuery')
                ->setParameter('searchQuery', '%'. $searchQuery .'%');
        }

        return $queryBuilder->getQuery()
            ->execute();
    }
}
