<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaItem;

use Backend\Core\Engine\Model;
use InvalidArgumentException;

enum Type: string
{
    case IMAGE = 'image';
    case FILE = 'file';
    case MOVIE = 'movie';
    case AUDIO = 'audio';

    public function getHtml(): string
    {
        return match ($this) {
            self::AUDIO => '<audio src="%s" preload="none" controls class="container"></audio>',
            self::FILE => '
              <a href="%s">
                <span class="fa fa-file" aria-hidden="true"></span>
              </a>
            ',
            self::IMAGE => '
              <picture class="container">
                <source srcset="%1$s.webp" type="image/webp">
                <img src="%1$s">
              </picture>
            ',
            self::MOVIE => '<video preload="none" controls  class="container"><source src="%s"></video>',
        };
    }

    public static function fromMimeType(string $mimeType): self
    {
        return match (true) {
            str_starts_with($mimeType, 'image') => self::IMAGE,
            str_starts_with($mimeType, 'video') => self::MOVIE,
            str_starts_with($mimeType, 'audio') => self::AUDIO,
            default => self::FILE,
        };
    }
}
