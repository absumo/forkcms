<?php

namespace ForkCMS\Modules\MediaLibrary\Backend\Actions;

use ForkCMS\Core\Domain\Header\FlashMessage\FlashMessage;
use ForkCMS\Modules\Backend\Domain\Action\AbstractFormActionController;
use ForkCMS\Modules\Backend\Domain\Action\ActionServices;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\Command\CreateMediaFolder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\Form\MediaFolderType;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolderRepository;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\NavigationBuilder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MediaFolderAdd extends AbstractFormActionController
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

        $parent = $request->query->has('folder')
            ? $this->mediaFolderRepository->find($request->query->getInt('folder', 1))
            : null;

        return $this->handleForm(
            request: $request,
            formType: MediaFolderType::class,
            formData: new CreateMediaFolder($parent),
            flashMessage: FlashMessage::success('Added'),
            redirectResponse: new RedirectResponse(MediaItemIndex::getActionSlug()->generateRoute($this->router))
        );
    }
}
