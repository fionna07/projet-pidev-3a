<?php

namespace App\Form;

use App\Entity\OffreEmploi;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\GreaterThan;

class OffreEmploiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de l\'offre',
                'attr' => ['placeholder' => 'Entrez le titre de l\'offre'],
                'constraints' => [
                    new NotBlank(['message' => 'Le titre est obligatoire.']),
                    new Length([
                        'min' => 3,
                        'max' => 40,
                        'maxMessage' => 'Le titre ne doit pas dépasser 40 caractères.',
                        'minMessage' => 'Le titre doit avoir au minimum 3 caractères.',

                    ]),
                    new Regex([
                        'pattern' => "/^[A-Za-zÀ-ÿ' ]+$/",
                        'message' => 'Le titre ne doit contenir que des lettres.',
                    ]),
                ],
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'attr' => ['placeholder' => 'Entrez une description'],
                'constraints' => [
                    new NotBlank(['message' => 'La description est obligatoire.']),
                    new Length([
                        'min' => 40,
                        'minMessage' => 'La description doit contenir au moins 40 caractères.',
                    ]),
                    new Regex([
                        'pattern' => "/^(?![\d\s]+$)[A-Za-z0-9À-ÿ,.' ]+$/",
                        'message' => 'La description doit contenir au moins trois mots et ne doit pas être uniquement composée de chiffres.',
                    ]),
                ],
            ])
            ->add('nombrePostes', NumberType::class, [
                'label' => 'Nombre de postes',
                'constraints' => [
                    new NotBlank(['message' => 'Le nombre de postes est obligatoire.']),
                    new GreaterThan([
                        'value' => 2,
                        'message' => 'Le nombre de postes doit être supérieur à 2.',
                    ]),
                ],
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'empty_data' => null,
                'invalid_message' => 'La date de début doit être une date valide.',
                'constraints' => [
                    new NotBlank(['message' => 'La date de début est obligatoire.']),
                    new GreaterThan([
                        'value' => 'today',
                        'message' => 'La date de début doit être supérieure à la date actuelle.',
                    ]),
                ],
            ])
            ->add('dateFinEstimee', DateType::class, [
                'label' => 'Date de fin estimée',
                'widget' => 'single_text',
                'empty_data' => null,
                'invalid_message' => 'La date de fin estimée doit être une date valide.',
                'constraints' => [
                    new NotBlank(['message' => 'La date de fin estimée est obligatoire.']),
                    new GreaterThan([
                        'propertyPath' => 'parent.all[dateDebut].data',
                        'message' => 'La date de fin estimée doit être supérieure à la date de début.',
                    ]),
                ],
            ])
            ->add('localisation', ChoiceType::class, [
                'label' => 'Localisation',
                'choices' => [
                    'Ariana' => 'Ariana',
                    'Béja' => 'Béja',
                    'Ben Arous' => 'Ben Arous',
                    'Bizerte' => 'Bizerte',
                    'Gabès' => 'Gabès',
                    'Gafsa' => 'Gafsa',
                    'Jendouba' => 'Jendouba',
                    'Kairouan' => 'Kairouan',
                    'Kasserine' => 'Kasserine',
                    'Kébili' => 'Kébili',
                    'Le Kef' => 'Le Kef',
                    'Mahdia' => 'Mahdia',
                    'La Manouba' => 'La Manouba',
                    'Médenine' => 'Médenine',
                    'Monastir' => 'Monastir',
                    'Nabeul' => 'Nabeul',
                    'Sfax' => 'Sfax',
                    'Sidi Bouzid' => 'Sidi Bouzid',
                    'Siliana' => 'Siliana',
                    'Sousse' => 'Sousse',
                    'Tataouine' => 'Tataouine',
                    'Tozeur' => 'Tozeur',
                    'Tunis' => 'Tunis',
                    'Zaghouan' => 'Zaghouan',
                ],
                'placeholder' => 'Sélectionnez un gouvernorat',
                'constraints' => [
                    new NotBlank(['message' => 'La localisation est obligatoire.']),
                ],
            ])
            ->add('competencesRequises', TextType::class, [
                'label' => 'Compétences requises (séparées par des virgules)',
                'attr' => ['placeholder' => 'Ex: Plantation de blé, Entretien des cultures,...'],
                'constraints' => [
                new NotBlank(['message' => 'Les compétences requises sont obligatoires.']),
                new Regex([
                    'pattern' => "/.+,.+/",
                    'message' => 'Il doit y avoir au moins deux compétences séparées par une virgule.',
                ]),
            ],

            ])
            ->add('salaire', NumberType::class, [
                'label' => 'Salaire',
                'constraints' => [
                    new NotBlank(['message' => 'Le salaire est obligatoire.']),
                    new GreaterThan([
                        'value' => 0,
                        'message' => 'Le salaire doit être supérieur à zéro.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OffreEmploi::class,
        ]);
    }
}