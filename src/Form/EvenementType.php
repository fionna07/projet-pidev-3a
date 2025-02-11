<?php

namespace App\Form;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'attr' => [
                    'placeholder' => 'Entrez le titre de l\'événement',
                    'class' => 'form-control',
                ],
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'Décrivez l\'événement en détail',
                    'class' => 'form-control',
                    'rows' => 5,
                ],
            ])
            ->add('dateDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('typeVisite', ChoiceType::class, [
                'choices' => [
                    'Sur place' => 'sur_place',
                    'Virtuelle' => 'virtuelle',
                ],
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Sélectionnez un type de visite', // Optionnel, améliore l'UX
            ])
            ->add('localisation', TextType::class, [
                'attr' => [
                    'placeholder' => 'Entrez la localisation de l\'événement',
                    'class' => 'form-control',
                ],
            ])
            ->add('prix', MoneyType::class, [
                'currency' => 'EUR',
                'attr' => [
                    'placeholder' => 'Entrez le prix de l\'événement',
                    'class' => 'form-control',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}
