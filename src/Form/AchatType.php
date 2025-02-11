<?php

namespace App\Form;

use App\Entity\Achat;
use App\Entity\Equipement;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class AchatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'html5' => true,
                'constraints' => [
                    new NotBlank(['message' => 'La date ne peut pas être vide.']),
                ],

            ])
            ->add('total', null, [
                'constraints' => [
                    new NotBlank(['message' => 'Le total ne peut pas être vide.']),
                    new Length(['min' => 1, 'minMessage' => 'Le total doit contenir au moins {{ limit }} chiffres.']),
                ],
            ])
            ->add('utilisateur', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => function(Utilisateur $utilisateur) {
                    return $utilisateur->getId() . ' - ' . $utilisateur->getFirstname() . ' ' . $utilisateur->getLastname();
                },
                'placeholder' => 'Choisir un utilisateur',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner un utilisateur.']),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Achat::class,
        ]);
    }
}
