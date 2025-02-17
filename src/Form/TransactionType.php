<?php

namespace App\Form;

use App\Entity\Transaction;
use App\Entity\Utilisateur; // On utilise Utilisateur au lieu de Client et Agriculteur
use App\Entity\Terrain;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\DateType;
class TransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('client', EntityType::class, [
                'class' => Utilisateur::class, // On prend les utilisateurs
               
                'choice_label' => 'nom', 
                'label' => 'Client'
            ])
        
            ->add('type', TextType::class, [
                'label' => 'Type',
            ])
            ->add('dateTransaction', DateType::class, [
                'widget' => 'single_text', // Permet d'afficher un champ de date
                'label' => 'Date de la Transaction',
                'required' => true,
            ])
            ->add('montant', MoneyType::class, [
                'label' => 'Montant'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
        ]);
    }
}
