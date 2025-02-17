<?php

namespace App\Form;

use App\Entity\Transaction;
use App\Entity\Utilisateur; // On utilise Utilisateur au lieu de Client et Agriculteur
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class TransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Sélectionner le client (Utilisateur ayant le rôle ROLE_CLIENT)
            ->add('client', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'firstName', // On peut changer selon l'attribut à afficher
                'label' => 'Client',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :role')
                        ->setParameter('role', '%ROLE_CLIENT%');
                },
            ])
        
            // Type de transaction (Vente, Location)
            ->add('type', TextType::class, [
                'label' => 'Type de transaction',
            ])

            // Date de la transaction (DateTimeType pour correspondre avec l'entité)
            ->add('dateTransaction', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de la Transaction',
                'required' => true,
            ])
            
            // Montant de la transaction (avec un champ MoneyType pour les montants monétaires)
            ->add('montant', MoneyType::class, [
                'label' => 'Montant de la transaction',
                'currency' => 'EUR', // Optionnel : pour définir la devise
            ])

            // Bouton pour soumettre le formulaire
            ->add('save', SubmitType::class, ['label' => 'Créer la transaction']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
        ]);
    }
}
