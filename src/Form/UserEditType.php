<?php
namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert; 
class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email', EmailType::class, [
            'label' => 'Email',
            'attr' => [
                'class' => 'form-control',
                'placeholder' => 'exemple@gmail.com' 
            ],
            'required' => false, 
            'constraints' => [
                new Assert\Email([
                    'message' => 'L\'adresse email "{{ value }}" n\'est pas valide.',
                    'mode' => 'strict',
                ]),
            ],
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
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'attr' => ['class' => 'form-control'],
                'required' => false, // Ne force pas la modification
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^\d+\s+[a-zA-Z\s]+,\s*\d{4,5}$/',
                        'message' => 'L\'adresse doit commencer par un numéro, suivie du nom de la rue, et se terminer par un code postal (4 ou 5 chiffres).',
                    ]),
                ],
            ])
            ->add('Password', PasswordType::class, [
                'mapped' => false, 
                'required' => false, // Laisser vide si l'utilisateur ne veut pas changer
               
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Laissez vide pour ne pas changer',
                    'value' => '********', 
                ],
            ])
            
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['class' => 'form-control'],
                'required' => false,
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/',
                        'message' => 'Le prénom ne doit contenir que des lettres, des espaces et des tirets.',
                    ]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control'],
                'required' => false, 
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/',
                        'message' => 'Le nom ne doit contenir que des lettres, des espaces et des tirets.',
                    ]),
                ],
            ])
            ->add('dateNaissance', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'required' => false, 
                'constraints' => [
                    new Assert\LessThanOrEqual([
                        'value' => '-18 years',
                        'message' => 'Vous devez avoir au moins 18 ans.',
                    ]),
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image de profil',
                'mapped' => false,
                'required' => false, 
               
            ])
            
            ->add('dateCreation', DateType::class, [
                'label' => 'Date de création',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'disabled' => true, // Empêcher la modification
            ])
            ->add('numTel', TelType::class, [
                'label' => 'Numéro de téléphone',
                'attr' => ['class' => 'form-control'],
                'required' => false, 
                'constraints' => [
                    new Assert\Length([
                        'min' => 8,
                        'max' => 8,
                        'exactMessage' => 'Le numéro de téléphone doit contenir exactement 8 chiffres.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[0-9]{8}$/',
                        'message' => 'Le numéro de téléphone ne doit contenir que des chiffres et être composé de 8 chiffres.',
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
