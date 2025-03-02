<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('image', FileType::class, [
            'label' => 'Photo de profil',
            'mapped' => false,
            'required' => false,
            'attr' => ['class' => 'form-control'],
        ])
        
            ->add('email', EmailType::class, [
            ])
            ->add('firstName', TextType::class, [
            ])
            ->add('lastName', TextType::class, [

            ])
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text', // Afficher comme un input de type date
                'format' => 'yyyy-MM-dd',  // Format de date
                'required' => true, // Champ obligatoire
                'empty_data' => null, // Permet une valeur nulle
                'attr' => ['class' => 'form-control shadow-sm rounded-pill'],
                'label' => 'Date de naissance',
                'constraints' => [
                    new NotBlank(['message' => 'La date de naissance est obligatoire.']),
                ],
            ])
            ->add('adresse', TextType::class, [
                
            ])
            ->add('numTel', IntegerType::class, [
                
            ])
         
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Agriculteur' => 'ROLE_AGRICULTEUR',
                    'Employé' => 'ROLE_EMPLOYE',
                    'Client' => 'ROLE_CLIENT',
                    'Fournisseur' => 'ROLE_FOURNISSEUR',
                ],
                'expanded' => true,  // Pour afficher sous forme de cases à cocher
                'multiple' => true,  // Pour permettre plusieurs sélections
                'required' => true,
            ])
            
            
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
