<?php

namespace App\Form;

use App\Entity\Achat;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AchatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date d\'achat',
                'required' => true,
                'data' => new \DateTimeImmutable()
            ])
            ->add('total', NumberType::class, [
                'label' => 'Total',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'step' => '0.01'
                ]
            ])
            ->add('utilisateur', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'firstname',
                'label' => 'Utilisateur',
                'required' => true,
                'placeholder' => 'Sélectionnez un utilisateur'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Achat::class,
            'csrf_protection' => true,
            'attr' => [
                'novalidate' => 'novalidate'
            ]
        ]);
    }
}
