<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaFolder;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\Exception\MediaFolderNotFound;

/**
 * @method MediaFolder|null find($id, $lockMode = null, $lockVersion = null)
 * @method MediaFolder|null findOneBy(array $criteria, array $orderBy = null)
 * @method MediaFolder[] findAll()
 * @method MediaFolder[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends ServiceEntityRepository<MediaFolder>
 */
final class MediaFolderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, MediaFolder::class);
    }

    public function save(MediaFolder $mediaFolder): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($mediaFolder);
        $entityManager->flush();
    }

    public function remove(MediaFolder $mediaFolder): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($mediaFolder);
        $entityManager->flush();
    }

    private function bumpFolderCount(int $folderId, array &$counts): void
    {
        // Counts for folder doesn't exist
        if (!array_key_exists($folderId, $counts)) {
            // Init counts
            $counts[$folderId] = 1;

            return;
        }

        // Bump counts
        ++$counts[$folderId];
    }

    /**
     * Does a folder exists by name?
     *
     * @param string $name The requested folder name to check if exists.
     * @param MediaFolder|null $parent The parent MediaFolder where this folder should be in.
     */
    public function existsByName(string $name, MediaFolder $parent = null): bool
    {
        /** @var MediaFolder $mediaFolder */
        $mediaFolder = $this->findOneBy([
            'name' => $name,
            'parent' => $parent,
        ]);

        return $mediaFolder instanceof MediaFolder;
    }

    public function findDefault(): MediaFolder
    {
        return $this->findBy([], ['name' => 'ASC'], 1)[0];
    }

    public function findOneById(int $id = null): MediaFolder
    {
        if ($id === null) {
            throw MediaFolderNotFound::forEmptyId();
        }

        $mediaFolder = $this->find($id);

        if (!$mediaFolder instanceof MediaFolder) {
            throw MediaFolderNotFound::forId($id);
        }

        return $mediaFolder;
    }

    /** @return MediaFolder[] */
    public function findTopLevel(): array
    {
        return $this->findBy(['parent' => null]);
    }
}
