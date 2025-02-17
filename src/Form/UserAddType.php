<?php
namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints as Assert;

class UserAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', null, [
                'constraints' => [
                
                    new Assert\Email(['message' => 'L\'email "{{ value }}" n\'est pas valide.']),
                ],
            ])
            ->add('password', PasswordType::class, [
                'constraints' => [
                  
                    new Assert\Length([
                        'min' => 8,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/',
                        'message' => 'Le mot de passe doit contenir au moins une lettre, un chiffre et un caractère spécial.',
                    ]),
                ],
            ])
            ->add('firstName', null, [
                'constraints' => [
                  
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/',
                        'message' => 'Le prénom ne doit contenir que des lettres, des espaces et des tirets.',
                    ]),
                ],
            ])
            ->add('lastName', null, [
                'constraints' => [
                
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/',
                        'message' => 'Le nom ne doit contenir que des lettres, des espaces et des tirets.',
                    ]),
                ],
            ])
            ->add('adresse', null, [
                'constraints' => [
               
                    new Assert\Regex([
                        'pattern' => '/^\d+\s+[a-zA-Z\s]+,\s*\d{4,5}$/',
                        'message' => 'L\'adresse doit commencer par un chiffre ou un nombre, suivie de chaînes de caractères, et se terminer par un code postal (4 ou 5 chiffres).',
                    ]),
                ],
            ])
            ->add('numTel', null, [
                'constraints' => [
               
                    new Assert\Regex([
                        'pattern' => '/^[259]\d{7}$/',
                        'message' => 'Le numéro de téléphone doit commencer par 2, 5 ou 9 et contenir 8 chiffres.',
                    ]),
                ],
            ])
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'required' => false,
                'attr' => ['class' => 'form-control shadow-sm rounded-pill'],
                'label' => 'Date de naissance',
                'data' => new \DateTime(), 

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
               
            ])
            ->add('image', FileType::class, [
                'label' => 'Photo de profil (JPEG, PNG)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image au format JPG ou PNG.',
                    ]),
                ],
            ])
            ->add('isVerified', CheckboxType::class, [
                'required' => false,
                'label' => 'Compte vérifié ?',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}