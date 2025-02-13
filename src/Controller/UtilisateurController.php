<?php

namespace App\Controller;
use App\Entity\Utilisateur;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
/////////////////register////////////////
use App\Form\ProfileEditType;

use App\Form\RegisterType;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
class UtilisateurController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function home(): Response
    {
        return $this->render('utilisateur/index.html.twig');
    }
   /* #[Route('/login', name: 'app_login')]
public function login(AuthenticationUtils $authenticationUtils): Response
{
    if ($this->getUser()) {
        return $this->redirectToRoute('app_userprofile'); // 🔹 Redirige vers le profil si déjà connecté
    }

    // Récupère une éventuelle erreur d'authentification
    $error = $authenticationUtils->getLastAuthenticationError();
    $lastUsername = $authenticationUtils->getLastUsername();

    return $this->render('security/login.html.twig', [
        'last_username' => $lastUsername,
        'error' => $error,
    ]);
}*/

    #[Route('/profile', name: 'app_userprofile')]
    public function userProfile(): Response
    {
        $user = $this->getUser();
    
        if (!$user instanceof Utilisateur) {  // Vérifie que c'est un utilisateur valide
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }
    
        return $this->render('utilisateur/UserProfile.html.twig', [
            'UserDetail' => $user,  // Passe l'utilisateur connecté à la vue
        ]);
    }
        #[Route('/delete-account', name: 'app_delete_account')]
        public function deleteAccount(EntityManagerInterface $entityManager): Response
        {
            $user = $this->getUser();
        
            if ($user) {
                $entityManager->remove($user);  // Suppression de l'utilisateur
                $entityManager->flush();
                
                $this->addFlash('success', 'Votre compte a été supprimé avec succès.');
            } else {
                $this->addFlash('error', 'Utilisateur non trouvé.');
            }
        
            return $this->redirectToRoute('app_home');  // Redirige vers la page d'accueil
        }
        #[Route('/disable-account', name: 'app_disable_account')]
        public function disableAccount(EntityManagerInterface $entityManager): Response
        {
            $user = $this->getUser();
             // Vérifie si l'utilisateur existe
            if ($user instanceof Utilisateur) {
                $user->setStatus('descativé');  // Désactive l'utilisateur
                $entityManager->flush();

                $this->addFlash('success', 'Votre compte a été désactivé temporairement.');
            } else {
                $this->addFlash('error', 'Utilisateur non trouvé.');
            }

            return $this->redirectToRoute('app_home');  // Redirige vers la page d'accueil
        }
        #[Route('/edit-profile', name: 'app_edit_profile')]
        public function editProfile(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
        {
            $user = $this->getUser();
        
            if (!$user instanceof Utilisateur) {  // Vérifie que c'est bien un utilisateur de ton application
                throw $this->createNotFoundException('Utilisateur non trouvé.');
            }
        
            $form = $this->createForm(ProfileEditType::class, $user);
            $form->handleRequest($request);
        
            if ($form->isSubmitted() && $form->isValid()) {
                // Gestion du mot de passe si changé
                $password = $form->get('password')->getData();
                if (!empty($password)) {
                    $hashedPassword = $userPasswordHasher->hashPassword($user, $password);
                    $user->setPassword($hashedPassword);
                }
        
                // Gestion de l'image de profil
                $imageFile = $form['image']->getData();
                if ($imageFile) {
                    try {
                        $destinationFolder = $this->getParameter('images_directory');
                        $filename = md5(uniqid()) . '.' . $imageFile->guessExtension();
                        $imageFile->move($destinationFolder, $filename);
                        $user->setImage($filename);
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Erreur lors du téléversement de l\'image : ' . $e->getMessage());
                    }
                }
        
                $entityManager->flush();
                $this->addFlash('success', 'Votre profil a été mis à jour.');
        
                return $this->redirectToRoute('app_userprofile');
            }
        
            return $this->render('utilisateur/profileEdit.html.twig', [
                'user' => $user,  // 🔹 Ajout de la variable user pour éviter l'erreur
                'form' => $form->createView(),
            ]);
        }        
        

 
}
