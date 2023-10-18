<?php

namespace ForkCMS\Modules\MediaLibrary\Backend\Actions;

use ForkCMS\Core\Domain\Header\FlashMessage\FlashMessage;
use ForkCMS\Modules\Backend\Domain\Action\AbstractFormActionController;
use ForkCMS\Modules\Backend\Domain\Action\ActionServices;
use ForkCMS\Modules\Backend\Domain\Action\ActionSlug;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\NavigationBuilder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Command\UpdateMediaItem;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Form\MediaItemTitleType;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MediaItemEdit extends AbstractFormActionController
{
    protected int $folderId;

    protected string $mediaItem;

    public function __construct(
        ActionServices $actionServices,
        private readonly NavigationBuilder $navigationBuilder,
    ) {
        parent::__construct($actionServices);
    }

    protected function getFormResponse(Request $request): ?Response
    {
        $this->assign('sidebarTree', $this->navigationBuilder->getTree(Locale::current()));

        /** @var MediaItem $mediaItem */
        $mediaItem = $this->getEntityFromRequest($request, MediaItem::class);

        $this->assign('media_item', $mediaItem);

        if ($this->getRepository(MediaItem::class)->count([]) > 1) {
            $this->addDeleteForm(
                ['id' => $mediaItem->getId()],
                ActionSlug::fromFQCN(MediaItemDelete::class)
            );
        }

        return $this->handleForm(
            request: $request,
            formType: MediaItemTitleType::class,
            formData: new UpdateMediaItem($mediaItem),
            flashMessage: FlashMessage::success('Edited'),
            redirectResponse: new RedirectResponse(MediaItemIndex::getActionSlug()->generateRoute($this->router)),
        );
    }
}
