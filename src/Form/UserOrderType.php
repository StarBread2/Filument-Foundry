<?php

namespace App\Form;

use App\Entity\Color;
use App\Entity\Finish;
use App\Entity\Material;
use App\Entity\UserOrder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('filename')
            ->add('material', EntityType::class, [
                'class' => Material::class,
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('finish', EntityType::class, [
                'class' => Finish::class,
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('color', EntityType::class, [
                'class' => Color::class,
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserOrder::class,
        ]);
    }
}
