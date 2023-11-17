<?php

namespace ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\Form;

use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;
use ForkCMS\Modules\MediaLibrary\Domain\MediaFolder\MediaFolderDataTransferObject;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaFolderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'label' => 'lbl.Title',
        ]);
        $builder->add('parent', EntityType::class, [
            'class' => MediaFolder::class,
            'label' => 'lbl.ParentFolder',
            'required' => false,
            'choice_label' => 'completeName',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => MediaFolderDataTransferObject::class,
            ]
        );
    }
}
