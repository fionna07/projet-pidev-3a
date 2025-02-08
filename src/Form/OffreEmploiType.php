<?php

namespace App\Form;

use App\Entity\OffreEmploi;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class OffreEmploiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de l\'offre',
                'attr' => ['placeholder' => 'Entrez le titre de l\'offre'],
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'attr' => ['placeholder' => 'Entrez une description'],
            ])
            ->add('nombrePostes', null, [
                'label' => 'Nombre de postes',
            ])
            ->add('dateDebut', null, [
                'label' => 'Date de début',
                'widget' => 'single_text',
            ])
            ->add('dateFinEstimee', null, [
                'label' => 'Date de fin estimée',
                'widget' => 'single_text',
            ])
            ->add('localisation', TextType::class, [
                'label' => 'Localisation',
            ])
            ->add('competencesRequises', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => ['label' => 'Compétence'],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Compétences requises',
            ])
            ->add('salaire', null, [
                'label' => 'Salaire',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OffreEmploi::class,
        ]);
    }
}
