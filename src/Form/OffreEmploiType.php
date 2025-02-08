<?php

namespace App\Form;

use App\Entity\OffreEmploi;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OffreEmploiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('description')
            ->add('nombrePostes')
            ->add('dateDebut', null, [
                'widget' => 'single_text',
            ])
            ->add('dateFinEstimee', null, [
                'widget' => 'single_text',
            ])
            ->add('status')
            ->add('localisation')
            ->add('datePublication', null, [
                'widget' => 'single_text',
            ])
            ->add('competencesRequises')
            ->add('salaire')
            ->add('user', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OffreEmploi::class,
        ]);
    }
}
