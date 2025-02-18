<?php

namespace App\Form;

use App\Entity\Terrain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TerrainType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
            ])
            ->add('surface', NumberType::class, [
                'label' => 'Surface (m²)',
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix',
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Disponible' => 'disponible',
                    'Vendu' => 'vendu',
                    'Réservé' => 'reserve',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label'    => 'Description',
                'required' => false,
            ])
            ->add('latitude', TextType::class, [
                'label'    => 'Latitude',
                'required' => false,
            ])
            ->add('longitude', TextType::class, [
                'label'    => 'Longitude',
                'required' => false,
            ])
            ->add('typeSol', ChoiceType::class, [
                'label' => 'Type de sol',
                'choices' => [
                    'Sableux'  => 'sableux',
                    'Argileux' => 'argileux',
                    'Alluvieux'=> 'alluvieux',
                    'Calcaire' => 'calcaire',
                ],
                'placeholder' => 'Choisissez le type de sol',
            ])
            ->add('photos', FileType::class, [
                'label'    => 'Photo du terrain',
                'mapped'   => false, // Ce champ n'est pas directement lié à l'entité
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-success']
            ])
            
            
          ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Terrain::class,
        ]);
    }
}
