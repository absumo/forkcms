<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaItem;

use Doctrine\DBAL\Types\Types;
use ForkCMS\Core\Domain\Settings\EntityWithSettingsTrait;
use ForkCMS\Modules\Backend\Domain\Action\ModuleAction;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Command\UpdateMediaItem;
use Pageon\DoctrineDataGridBundle\Attribute\DataGrid;
use Pageon\DoctrineDataGridBundle\Attribute\DataGridActionColumn;
use Pageon\DoctrineDataGridBundle\Attribute\DataGridMethodColumn;
use Pageon\DoctrineDataGridBundle\Attribute\DataGridPropertyColumn;
use BadFunctionCallException;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Uid\Uuid;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: MediaItemRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[DataGrid('MediaItem')]
#[DataGridActionColumn(
    route: 'backend_action',
    routeAttributes: [
        'module' => 'media-library',
        'action' => 'media-item-edit',
    ],
    routeAttributesCallback: [self::class, 'dataGridEditLinkCallback'],
    label: 'lbl.Rename',
    class: 'btn btn-sm btn-primary me-2',
    iconClass: 'fa fas fa-edit me-0',
    requiredRole: ModuleAction::ROLE_PREFIX . 'BACKEND__MEDIA_ITEM_EDIT',
    columnAttributes: ['class' => 'fork-data-grid-action'],
)]
#[DataGridActionColumn(
    route: 'backend_action',
    routeAttributes: [
        'module' => 'media-library',
        'action' => 'media-item-crop',
    ],
    routeAttributesCallback: [self::class, 'dataGridEditLinkCallback'],
    label: 'lbl.Crop',
    class: 'btn btn-sm btn-primary me-2',
    iconClass: 'fa fas fa-crop me-0',
    requiredRole: ModuleAction::ROLE_PREFIX . 'BACKEND__MEDIA_ITEM_EDIT',
    columnAttributesCallback: [self::class, 'dataGridCropCallback'],
)]
#[DataGridActionColumn(
    route: 'backend_action',
    routeAttributes: [
        'module' => 'media-library',
        'action' => 'media-item-edit', // TODO: link action
    ],
    routeAttributesCallback: [self::class, 'dataGridEditLinkCallback'],
    label: 'lbl.Link',
    class: 'btn btn-sm btn-primary me-2',
    iconClass: 'fa fas fa-link me-0',
    requiredRole: ModuleAction::ROLE_PREFIX . 'BACKEND__MEDIA_ITEM_EDIT',
    columnAttributes: ['class' => 'fork-data-grid-action'],
)]
#[DataGridActionColumn(
    route: 'backend_action',
    routeAttributes: [
        'module' => 'media-library',
        'action' => 'media-item-delete', // TODO: confirm delete action
    ],
    routeAttributesCallback: [self::class, 'dataGridEditLinkCallback'],
    label: 'lbl.Delete',
    class: 'btn btn-sm btn-danger ms-auto',
    iconClass: 'fa fas fa-trash-alt me-0',
    requiredRole: ModuleAction::ROLE_PREFIX . 'BACKEND__MEDIA_ITEM_DELETE',
    columnAttributes: ['class' => 'fork-data-grid-action'],
)]
#[Vich\Uploadable]
class MediaItem implements JsonSerializable
{
    // use EntityWithSettingsTrait;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    protected ?string $mime = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    protected ?int $size = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    protected ?int $width = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    protected ?int $height = null;

    #[ORM\Column(type: 'media_item_aspect_ratio', nullable: true)]
    protected ?AspectRatio $aspectRatio = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected DateTime $createdOn;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected DateTime $editedOn;

    #[ORM\Column(enumType: Type::class)]
    protected Type $type;

    #[ORM\Column(enumType: StorageType::class, options: ['default' => StorageType::LOCAL])]
    protected StorageType $storageType;

    #[Vich\UploadableField(mapping: 'files', fileNameProperty: 'path', size: 'size', mimeType: 'mime', originalName: 'title', dimensions: 'dimensions')]
    private ?File $file = null;

    private function __construct(
        #[DataGridPropertyColumn(sortable: true, filterable: true, label: 'lbl.Title', class: 'small')]
        #[ORM\Column(type: Types::STRING)]
        protected string $title,
        #[ORM\Column(type: Types::STRING)]
        protected string $path,
        #[ORM\ManyToOne(targetEntity: MediaFolder::class, cascade: ['persist'], inversedBy: 'items')]
        #[ORM\JoinColumn(name: 'mediaFolderId', referencedColumnName: 'id', nullable: false, onDelete: 'cascade')]
        protected MediaFolder $folder,
        #[ORM\Column(type: Types::INTEGER)]
        protected int $userId
    ) {
        $this->createdOn = new DateTime();
        $this->editedOn = new DateTime();
        $this->storageType = StorageType::LOCAL;
    }

    public static function createFromFile(
        File $file,
        MediaFolder $folder,
        int $userId
    ): self {
        $mediaItem = new self(
            self::getTitleFromFile($file),
            $file->getFilename(),
            $folder,
            $userId
        );
        $mediaItem->setFile($file);

        return $mediaItem;
    }

    /** Called by Vich */
    public function setMime(?string $mime): void
    {
        if ($mime === null) {
            return;
        }
        $this->mime = $mime;
        $this->type = Type::fromMimeType($mime);
    }

    /** Called by Vich */
    public function setDimensions(?array $dimensions): void
    {
        if ($dimensions === null) {
            return;
        }
        $this->width = $dimensions[0] ?? null;
        $this->height = $dimensions[1] ?? null;
        $this->refreshAspectRatio();
    }

    public function setSize(?int $size): void
    {
        $this->size = $size;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): void
    {
        $this->file = $file;
    }

    public static function fromDataTransferObject(MediaItemDataTransferObject $mediaItemDataTransferObject): ?MediaItem
    {
        if (!$mediaItemDataTransferObject->hasExistingMediaItem()) {
            throw new BadFunctionCallException('This method can not be used to create a new media item');
        }

        $mediaItem = $mediaItemDataTransferObject->getMediaItemEntity();

        $mediaItem->title = $mediaItemDataTransferObject->title;
        $mediaItem->folder = $mediaItemDataTransferObject->folder;
        $mediaItem->userId = $mediaItemDataTransferObject->userId;
        if ($mediaItemDataTransferObject instanceof UpdateMediaItem && $mediaItemDataTransferObject->vich !== null) {
            $mediaItem->setFile($mediaItemDataTransferObject->vich);
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $mediaItem->editedOn = new DateTime();
        }

        return $mediaItem;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'folder' => $this->folder,
            'userId' => $this->userId,
            'type' => $this->type->value,
            'storageType' => $this->storageType->value,
            'mime' => $this->mime,
            'path' => $this->path,
            'title' => $this->title,
            'size' => $this->size,
            'width' => $this->width,
            'height' => $this->height,
            'createdOn' => $this->createdOn->getTimestamp(),
            'editedOn' => $this->editedOn->getTimestamp(),
            $this->type->value => true,
        ];
    }

    private static function getTitleFromFile(File $file): string
    {
        return str_replace('.' . $file->getExtension(), '', $file->getFilename());
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getFolder(): MediaFolder
    {
        return $this->folder;
    }

    public function setFolder(MediaFolder $folder): self
    {
        $this->folder = $folder;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getMime(): string
    {
        return $this->mime;
    }

    public function setPath(?string $path): void
    {
        if ($path !== null) {
            $this->path = $path;
        }
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        if ($title === null) {
            return;
        }
        $this->title = $title;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setResolution(int $width, int $height): self
    {
        $this->width = $width;
        $this->height = $height;

        $this->refreshAspectRatio();

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function getCreatedOn(): DateTime
    {
        return $this->createdOn;
    }

    #[DataGridMethodColumn(order: -1, label: 'lbl.LastEdited', class: 'fs-6')]
    public function getEditedOn(): DateTime
    {
        return $this->editedOn;
    }

    // #[DataGridPropertyColumn(label: 'lbl.File')]
    public readonly string $webpath;

    public function setWebPath(string $path): void
    {
        $this->webpath = $path;
    }

    #[DataGridMethodColumn(order: -2, label: 'lbl.File', class: 'file-icon d-flex justify-content-center align-items-center fs-2', html: true)]
    public function getPreview(): string
    {
        if ($this->type === Type::IMAGE) {
            return sprintf($this->type->getHtml(), $this->webpath);
        }

        return sprintf('<i class="fas %s" title="%s"></i>', $this->getFAIcon(), $this->mime);
    }

    private function getFAIcon(): string
    {
        return match ($this->type) {
            Type::MOVIE => 'fa-file-video',
            Type::AUDIO => 'fa-file-audio',
            Type::IMAGE => 'fa-file-image',
            default => match($this->mime) {
                'application/pdf' => 'fa-file-pdf',
                'application/zip', 'application/gzip', 'application/x-zip-compressed' => 'fa-file-archive',
                'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fa-file-word',
                'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'fa-file-excel',
                default => 'fa-file'
            }
        };
    }

    public function getAspectRatio(): AspectRatio
    {
        return $this->aspectRatio;
    }

    private function refreshAspectRatio(): void
    {
        if ($this->height === null || $this->width === null) {
            $this->aspectRatio = null;

            return;
        }

        $this->aspectRatio = AspectRatio::fromWidthAndHeight($this->width, $this->height);
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdOn = new DateTime();
        $this->editedOn = $this->createdOn;
        $this->refreshAspectRatio();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->editedOn = new DateTime();

        $this->refreshAspectRatio();
    }

    /**
     * @param array{string?: string} $attributes
     *
     * @return array{string?: int|string}
     */
    public static function dataGridEditLinkCallback(self $mediaItem, array $attributes): array
    {
        $attributes['slug'] = $mediaItem->getId();

        return $attributes;
    }

    public static function dataGridCropCallback(self $mediaItem): array
    {
        if ($mediaItem->type === Type::IMAGE) {
            return ['class' => 'fork-data-grid-action'];
        }

        return ['class' => 'd-none'];
    }
}
