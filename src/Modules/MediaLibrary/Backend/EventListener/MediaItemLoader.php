<?php

namespace ForkCMS\Modules\MediaLibrary\Backend\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Events;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItem;
use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Type;
use League\Flysystem\Filesystem;
use Liip\ImagineBundle\Service\FilterService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsDoctrineListener(event: Events::postLoad)]
class MediaItemLoader
{
    public function __construct(
        #[Autowire(service: 'liip_imagine.service.filter')]
        private readonly FilterService $filterService,
        #[Autowire(service: 'media_library.storage')]
        private readonly Filesystem $filesystem,
    ) {}

    // the listener methods receive an argument which gives you access to
    // both the entity object of the event and the entity manager itself
    public function postLoad(PostLoadEventArgs $args): void
    {
        $entity = $args->getObject();

        // if this listener only applies to certain entity types,
        // add some code to check the entity type as early as possible
        if (!$entity instanceof MediaItem) {
            return;
        }

        if ($entity->getType() !== Type::IMAGE) {
            $entity->setWebPath($this->filesystem->publicUrl($entity->getPath()));

            return;
        }

        $entity->setWebPath(
            $this->filterService->getUrlOfFilteredImage($entity->getPath(), 'backend_card_image')
        );
    }
}