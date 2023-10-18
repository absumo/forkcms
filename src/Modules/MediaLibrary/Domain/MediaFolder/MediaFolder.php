<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaFolder;

use Doctrine\DBAL\Types\Types;
use Stringable;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItem;
use JsonSerializable;

/**
 * MediaFolder
 */
#[ORM\Entity(repositoryClass: MediaFolderRepository::class)]
#[ORM\HasLifecycleCallbacks]
class MediaFolder implements JsonSerializable, Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected DateTime $createdOn;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected DateTime $editedOn;

    /** @var Collection<array-key, MediaItem> */
    #[ORM\OneToMany(targetEntity: MediaItem::class, mappedBy: 'folder', cascade: ['persist', 'merge'], orphanRemoval: true)]
    protected Collection $items;

    /** @var Collection<array-key, MediaFolder> */
    #[ORM\OneToMany(targetEntity: MediaFolder::class, mappedBy: 'parent', cascade: ['persist', 'merge'], orphanRemoval: true)]
    protected Collection $children;

    /**
     * @param string $name The name of this folder.
     * @param MediaFolder|null $parent The parent of this folder, can be NULL.
     * @param int $userId The id of the user who created this MediaFolder.
     */
    protected function __construct(
        #[ORM\Column(type: Types::STRING)]
        protected string $name,
        #[ORM\ManyToOne(targetEntity: MediaFolder::class, inversedBy: 'children', cascade: ['persist'])]
        #[ORM\JoinColumn(name: 'parentMediaFolderId', referencedColumnName: 'id', onDelete: 'cascade')]
        protected ?MediaFolder $parent,
        #[ORM\Column(type: Types::INTEGER)]
        protected int $userId
    ) {
        $this->items = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public static function create(
        string $name,
        ?MediaFolder $parent,
        int $userId
    ) : MediaFolder {
        return new self(
            $name,
            $parent,
            $userId
        );
    }

    public function update(string $name, MediaFolder $parent = null): void
    {
        $this->name = $name;

        if ($parent instanceof self) {
            $this->setParent($parent);

            return;
        }

        $this->removeParent();
    }

    public static function fromDataTransferObject(
        MediaFolderDataTransferObject $mediaFolderDataTransferObject
    ): MediaFolder {
        if ($mediaFolderDataTransferObject->hasExistingMediaFolder()) {
            $mediaFolder = $mediaFolderDataTransferObject->getMediaFolderEntity();

            $mediaFolder->update(
                $mediaFolderDataTransferObject->name,
                $mediaFolderDataTransferObject->parent
            );

            return $mediaFolder;
        }

        return self::create(
            $mediaFolderDataTransferObject->name,
            $mediaFolderDataTransferObject->parent,
            $mediaFolderDataTransferObject->userId
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'parent' => $this->parent,
            'userId' => $this->userId,
            'name' => $this->name,
            'createdOn' => $this->createdOn->getTimestamp(),
            'editedOn' => $this->editedOn->getTimestamp(),
            'numberOfItems' => $this->getItems()->count(),
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getParent(): ?MediaFolder
    {
        return $this->parent;
    }

    public function hasParent(): bool
    {
        return $this->parent instanceof self;
    }

    public function removeParent(): self
    {
        $this->parent = null;

        return $this;
    }

    public function setParent(MediaFolder $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCreatedOn(): DateTime
    {
        return $this->createdOn;
    }

    public function getEditedOn(): DateTime
    {
        return $this->editedOn;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function hasItems(): bool
    {
        return $this->items->count() > 0;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function hasChildren(): bool
    {
        return $this->children->count() > 0;
    }

    #[ORM\PrePersist]
    public function onPrePersist() : void
    {
        $this->createdOn = new DateTime();
        $this->editedOn = $this->createdOn;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->editedOn = new DateTime();
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
