<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;

class MediaItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('vich', VichFileType::class, [
            'attr' => [
                'class' => 'fallback',
                // 'placeholder' => 'Drag and drop or browse',
            ],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'media_item';
    }
}
