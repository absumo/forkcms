<?php

namespace ForkCMS\Modules\MediaLibrary\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;
use ForkCMS\Modules\Internationalisation\Domain\Translation\TranslationKey;
use ForkCMS\Modules\MediaLibrary\Backend\Actions\MediaItemCrop;
use ForkCMS\Modules\MediaLibrary\Backend\Actions\MediaItemEdit;
use ForkCMS\Modules\MediaLibrary\Backend\Actions\MediaItemIndex;
use ForkCMS\Modules\MediaLibrary\Backend\Actions\MediaItemUpload;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\Command\CreateMediaFolder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItem;

final class MediaLibraryInstaller extends ModuleInstaller
{
    public function preInstall(): void
    {
        $this->createTableForEntities(
            MediaItem::class,
            MediaFolder::class,
        );
    }

    public function install(): void
    {
        $this->importTranslations(__DIR__ . '/../assets/installer/translations.xml');
        $this->configureSettings();
        $this->createBackendPages();
        $this->loadMediaFolders();
    }

    private function createBackendPages(): void
    {
        $this->getOrCreateBackendNavigationItem(
            label: TranslationKey::label('MediaLibrary'),
            slug: MediaItemIndex::getActionSlug(),
            selectedFor: [
                MediaItemUpload::getActionSlug(),
                MediaItemEdit::getActionSlug(),
                MediaItemCrop::getActionSlug(),
            ],
            sequence: 3,
        );
    }

    protected function configureSettings(): void
    {
        $this->setSetting('upload_number_of_sharding_folders', 15);
    }

    protected function loadMediaFolders(): void
    {
        $this->dispatchCommand(new CreateMediaFolder('default', 1));

        // TODO: Delete cache
    }
}
