<?php
namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
class ProfileEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    { $builder
        ->add('email', EmailType::class, [
            'required' => false,
            'constraints' => [
                new Assert\NotBlank(['message' => 'L\'email est obligatoire.']),
                new Assert\Email(['message' => 'Veuillez entrer un email valide.']),
            ],
        ])
        ->add('firstName', TextType::class, [
            'required' => false,
            'constraints' => [
                new Assert\NotBlank(['message' => 'Le prénom est obligatoire.']),
                new Assert\Length([
                    'min' => 2,
                    'minMessage' => 'Le prénom doit contenir au moins 2 caractères.',
                ]),
            ],
        ])
        ->add('lastName', TextType::class, [
            'required' => false,
            'constraints' => [
                new Assert\NotBlank(['message' => 'Le nom est obligatoire.']),
                new Assert\Length([
                    'min' => 2,
                    'minMessage' => 'Le nom doit contenir au moins 2 caractères.',
                ]),
            ],
        ])
        ->add('dateNaissance', DateType::class, [
            'required' => false,
            'widget' => 'single_text',
        ])
        ->add('adresse', TextType::class, [
            'required' => false,
            'constraints' => [
                new Assert\NotBlank(['message' => 'L\'adresse est obligatoire.']),
            ],
        ])
        ->add('numTel', IntegerType::class, [
            'required' => false,
            'constraints' => [
                new Assert\NotBlank(['message' => 'Le numéro de téléphone est obligatoire.']),
                new Assert\Length([
                    'min' => 8,
                    'max' => 15,
                    'minMessage' => 'Le numéro doit contenir au moins 8 chiffres.',
                    'maxMessage' => 'Le numéro ne peut pas dépasser 15 chiffres.',
                ]),
            ],
        ])
        ->add('image', TextType::class, [
            'required' => false,
            'mapped' => false,
        ])
        ->add('password', PasswordType::class, [
            'mapped' => false, 
            'required' => false, 
            'constraints' => [
                new Assert\Length([
                    'min' => 6,
                    'minMessage' => 'Le mot de passe doit contenir au moins 6 caractères.',
                ]),
            ],
            'attr' => [
                'autocomplete' => 'new-password',
                'placeholder' => 'Laissez vide pour ne pas changer',
                'value' => '********',
            ],
        ])
        ->add('confirmPassword', PasswordType::class, [
            'label' => 'Confirmer le mot de passe',
            'mapped' => false, // Ce champ n'est pas lié à l'entité
            
        ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
           
        ]);
           // Ajoutez cette ligne pour éviter l'erreur
    $resolver->setDefined('current_role');
    }
}
