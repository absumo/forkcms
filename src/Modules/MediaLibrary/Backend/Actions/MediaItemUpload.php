<?php

namespace ForkCMS\Modules\MediaLibrary\Backend\Actions;

use ForkCMS\Modules\Backend\Domain\Action\AbstractFormActionController;
use ForkCMS\Modules\Backend\Domain\Action\ActionServices;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolderRepository;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\NavigationBuilder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Command\UploadMediaItem;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Form\MediaItemType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MediaItemUpload extends AbstractFormActionController
{
    protected ?MediaFolder $mediaFolder;

    public function __construct(
        ActionServices $actionServices,
        private readonly NavigationBuilder $navigationBuilder,
        private readonly MediaFolderRepository $mediaFolderRepository,
    ) {
        parent::__construct($actionServices);
    }

    protected function getFormResponse(Request $request): ?Response
    {
        $this->assign('sidebarTree', $this->navigationBuilder->getTree(Locale::current()));

        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            $defaultCallback = function (FormInterface $form): JsonResponse {
                return new JsonResponse(['error' => (string) $form->getErrors(true)], Response::HTTP_BAD_REQUEST);
            };

            $validCallback = function (FormInterface $form): JsonResponse {
                $this->commandBus->dispatch($form->getData());

                return new JsonResponse(['error' => null]);
            };
        } else {
            $defaultCallback = null;
            $validCallback = function (FormInterface $form): RedirectResponse {
                $this->commandBus->dispatch($form->getData());

                return new RedirectResponse(MediaItemEdit::getActionSlug()->generateRoute($this->router, ['slug' => $form->getData()->page->getId()]));
            };
        }


        $mediaFolder = $this->mediaFolderRepository->find($request->request->getInt('folder', 1));
        $data = new UploadMediaItem();
        $data->mediaFolder = $mediaFolder;
        return $this->handleForm(
            request: $request,
            formType: MediaItemType::class,
            formData: $data,
            formOptions: ['attr' => ['class' => 'dropzone', 'id' => 'ml'], 'action' => self::getActionSlug()->generateRoute($this->router)],
            defaultCallback: $defaultCallback,
            validCallback: $validCallback,
        );
    }
}
