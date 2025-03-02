<?php

namespace App\Form;

use App\Entity\Equipement;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class EquipementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'équipement',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un nom',
                    ]),
                ],
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix (TND)',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un prix',
                    ]),
                    new Positive([
                        'message' => 'Le prix doit être positif',
                    ]),
                ],
            ])
            ->add('quantite_disponible', NumberType::class, [
                'label' => 'Quantité disponible',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer une quantité',
                    ]),
                    new Positive([
                        'message' => 'La quantité doit être positive',
                    ]),
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, GIF)',
                    ]),
                    new NotBlank([
                        'message' => 'Veuillez télécharger une image',
                    ])
                ],
            ])
            ->add('categorie', TextType::class, [
                'label' => 'Catégorie',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer une catégorie',
                    ]),
                ],
            ])
            ->add('fournisseur', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'firstname',
                'label' => 'Fournisseur',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner un fournisseur',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equipement::class,
        ]);
    }
}
