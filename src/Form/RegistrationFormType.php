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
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('image', FileType::class, [
            'label' => 'Photo de profil',
            'mapped' => false,
            'required' => true, // Obligatoire
            'attr' => ['class' => 'form-control'],
            'constraints' => [
                new NotBlank(['message' => 'Veuillez ajouter une image.']),
                new Assert\Image([
                    'maxSize' => '2M',
                    'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                    'mimeTypesMessage' => 'Seuls les fichiers JPG, PNG et GIF sont autorisés.',
                ]),
            ],
        ])
            ->add('email', EmailType::class, [
            ])
            ->add('firstName', TextType::class, [
            ])
            ->add('lastName', TextType::class, [

            ])
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'required' => false, // Facultatif
                'attr' => ['class' => 'form-control shadow-sm rounded-pill'],
                'label' => 'Date de naissance',
                'data' => new \DateTime(), // Définit une date par défaut (aujourd'hui)
                'invalid_message' => 'La date de fin estimée doit être une date valide.',
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
                'expanded' => true,
                'multiple' => true,
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
