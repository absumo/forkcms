<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\Twig;

use ForkCMS\Modules\MediaLibrary\Domain\MediaItem\MediaItemRepository;
use Symfony\Component\Uid\Uuid;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class MediaItemExtension extends AbstractExtension
{
    public function __construct(
        private readonly MediaItemRepository $mediaItemRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'media_item_url',
                $this->getMediaItemUrl(...),
            ),
        ];
    }

    private function getMediaItemUrl(string $id): ?string
    {
        return $this->mediaItemRepository->find(Uuid::fromString($id))?->webpath;
    }
}
