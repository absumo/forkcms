<?php

namespace ForkCMS\Modules\MediaLibrary\Installer;

use Backend\Core\Engine\Model;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use ForkCMS\Modules\MediaLibrary\Backend\Domain\MediaFolder\Command\CreateMediaFolder;
use ForkCMS\Modules\MediaLibrary\Backend\Domain\MediaFolder\MediaFolder;
use ForkCMS\Modules\MediaLibrary\Backend\Domain\MediaGroup\MediaGroup;
use ForkCMS\Modules\MediaLibrary\Backend\Domain\MediaGroupMediaItem\MediaGroupMediaItem;
use ForkCMS\Modules\MediaLibrary\Backend\Domain\MediaItem\MediaItem;

final class MediaLibraryInstaller extends ModuleInstaller
{
    public function preInstall(): void
    {
        $this->createTableForEntities(MediaItem::class);
    }

    public function install(): void
    {
        $this->importTranslations(__DIR__ . '/../assets/installer/translations.xml');
        $this->createBackendPages();
    }

    private function createBackendPages(): void
    {
        $this->getOrCreateBackendNavigationItem(
            label: TranslationKey::label('MediaLibrary'),
            slug: MediaItemIndex::getActionSlug(),
            selectedFor: [
                MediaItemAdd::getActionSlug(),
                MediaItemEdit::getActionSlug(),
                MediaItemDelete::getActionSlug(),
            ],
            sequence: 1,
        );
    }
}
