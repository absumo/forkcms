<?php

namespace ForkCMS\Modules\MediaLibrary\Backend\Actions;

use ForkCMS\Core\Domain\Header\FlashMessage\FlashMessage;
use ForkCMS\Modules\Backend\Domain\Action\AbstractFormActionController;
use ForkCMS\Modules\Backend\Domain\Action\ActionServices;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\NavigationBuilder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Command\CropMediaItem;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Command\UpdateMediaItem;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Command\UploadMediaItem;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Form\MediaItemCropType;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItem;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

class MediaItemCrop extends AbstractFormActionController
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

        if ($mediaItem->getType() !== Type::IMAGE) {
            throw new UnsupportedMediaTypeHttpException('Only images can be cropped.');
        }

        $this->assign('media_item', $mediaItem);

        $redirectResponse = new RedirectResponse(MediaItemIndex::getActionSlug()->generateRoute($this->router));
        $validCallback = function (FormInterface $form) use ($redirectResponse): ?Response {
            if ($form->get('save')->isClicked()) {
                $this->commandBus->dispatch(UpdateMediaItem::fromCrop($form->getData()));
            } elseif ($form->get('new')->isClicked()) {
                $this->commandBus->dispatch(UploadMediaItem::fromCrop($form->getData()));
            }

            return $redirectResponse;
        };

        return $this->handleForm(
            request: $request,
            formType: MediaItemCropType::class,
            formData: new CropMediaItem($mediaItem),
            flashMessage: FlashMessage::success('Cropped'),
            redirectResponse: $redirectResponse,
            validCallback: $validCallback,
        );
    }
}
