<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaItem\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class MediaItemCropType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('crop', FileType::class, [
            'required' => false,
            'attr' => [
                'class' => 'visually-hidden',
            ],
        ]);

        $builder->add('save', SubmitType::class);
        $builder->add('new', SubmitType::class, [
            'label' => 'SaveAsNew',
        ]);
    }
}
