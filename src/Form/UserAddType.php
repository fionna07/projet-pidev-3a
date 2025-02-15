<?php
namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'required' => true,
                'attr' => ['autocomplete' => 'new-password'],
                'mapped' => true, // Laisser à true si l'attribut existe dans l'entité Utilisateur
            ])
            
            ->add('firstName')
            ->add('lastName')
            ->add('adresse')
            ->add('numTel')
            ->add('dateNaissance', null, [
                'widget' => 'single_text'
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
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image au format JPG ou PNG',
                    ])
                ],
            ])
            ->add('isVerified', CheckboxType::class, [
                'required' => false,
                'label' => 'Compte vérifié ?'
            ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
