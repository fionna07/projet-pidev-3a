<?php

namespace App\Controller;
use App\Entity\Utilisateur;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
/////////////////register////////////////
use App\Form\ProfileEditType;
use App\Service\ImageUploader;
use App\Form\RegisterType;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Service\ActivityLoggerService;
//desactiver
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
class UtilisateurController extends AbstractController
{
    private ActivityLoggerService $activityLogger;
    public function __construct(ActivityLoggerService $activityLogger)
    {
        $this->activityLogger = $activityLogger;
        

    }
    #[Route('/home', name: 'home')]
    public function home(ActivityLoggerService $activityLogger): Response
    {
        $user = $this->getUser();
        
        if ($user instanceof Utilisateur) {
            $activityLogger->log('Accès à la page d’accueil',$user);
        } else {
            $activityLogger->log('Accès anonyme à la page d’accueil',null);
        }
    
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
public function userProfile(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher,ActivityLoggerService $activityLogger): Response
{
    $user = $this->getUser();
    
    if (!$user instanceof Utilisateur) {
        throw $this->createNotFoundException('Utilisateur non trouvé.');
    }
   
     $authorizedRoles = ['ROLE_FOURNISSANT', 'ROLE_CLIENT', 'ROLE_EMPLOYEE', 'ROLE_AGRICULTEUR','ROLE_USER'];
     if (!array_intersect($user->getRoles(), $authorizedRoles)) {
        $activityLogger->log('Accès refusé au profil',$user);
         throw $this->createAccessDeniedException('Vous n\'avez pas les droits nécessaires pour accéder à cette page.');
     }
     $activityLogger->log('Consultation du profil', $user);
 
    $form = $this->createForm(ProfileEditType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $plainPassword = $form->get('password')->getData();
        if (!empty($plainPassword)) {
            $hashedPassword = $userPasswordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
        }

 
        $imageFile = $form['image']->getData();
        if ($imageFile) {
            $destinationFolder = $this->getParameter('images_directory');
            $filename = md5(uniqid()) . '.' . $imageFile->guessExtension();
            $imageFile->move($destinationFolder, $filename);
            $user->setImage($filename);
        }

        $entityManager->flush();
        $this->addFlash('success', 'Votre profil a été mis à jour.');
        $activityLogger->log('Mise à jour du profil réussie',$user);

                return $this->redirectToRoute('app_userprofile');
        }

            return $this->render('utilisateur/UserProfile.html.twig', [
                'UserDetail' => $user,
                'form' => $form->createView(),
            ]);
        }

        #[Route('/delete-account', name: 'app_delete_account')]
        public function deleteAccount(EntityManagerInterface $entityManager,ActivityLoggerService $activityLogger): Response
        {
            $user = $this->getUser();
        
            if ($user instanceof Utilisateur) {
                $activityLogger->log('Suppression de compte demandée',$user);
                $entityManager->remove($user);  
                $entityManager->flush();
                
                $this->addFlash('success', 'Votre compte a été supprimé avec succès.');
            } else {
                $this->addFlash('error', 'Utilisateur non trouvé.');
            }
        
            return $this->redirectToRoute('app_login');  
        }

        #[Route('/disable-account', name: 'app_disable_account', methods:['GET', 'POST'])]
        public function disableAccount(Request $request, Security $security, EntityManagerInterface $entityManager, ActivityLoggerService $activityLogger,TokenStorageInterface $tokenStorage,SessionInterface $session): Response
        {
            $user = $this->getUser();
            if ($user instanceof Utilisateur) {
                $duration = $request->request->get('duration'); // Récupérer la durée
        
                // Sauvegarde la date de réactivation
                if ($duration === 'permanent') {
                    $user->setStatus('désactivé');
                    $user->setReactivationDate(null); // Indéfini
                } else {
                    $days = (int) $duration;
                    $reactivationDate = (new \DateTime())->modify("+{$days} days");
                    $user->setStatus('désactivé');
                    $user->setReactivationDate($reactivationDate);
                }
        
                $entityManager->flush();
        
                // Logger l'activité
                $activityLogger->log(
                    'Compte désactivé pour ' . ($duration === 'permanent' ? 'une durée indéterminée' : "{$duration} jours"),
                    $user
                );
        
                $this->addFlash('success', 'Votre compte a été désactivé pour ' . ($duration === 'permanent' ? 'une durée indéterminée' : "{$duration} jours") . '.');
        
                // ✅ Déconnexion automatique
                $tokenStorage->setToken(null); // Supprime le token d'authentification
                $session->invalidate(); // Détruit la session
        
                return $this->redirectToRoute('app_login'); // Redirige vers la page de login
            }
        
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('app_home');
        }
        
   

        #[Route('/edit-profile', name: 'app_edit_profile')]
        public function editProfile(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager,ImageUploader $uploader,ActivityLoggerService $activityLogger): Response
        {
            $user = $this->getUser(); 
           
            if (!$user instanceof Utilisateur) {
                throw $this->createAccessDeniedException('Vous devez être connecté en tant qu\'utilisateur pour accéder à cette page.');
            }

           
            $roles = array_filter($user->getRoles(), fn($role) => $role !== 'ROLE_USER');
            $mainRole = reset($roles); // Prend le premier rôle restant
        

          
            $form = $this->createForm(ProfileEditType::class, $user, [
                'current_role' => $mainRole, 
            ]);

                $form->handleRequest($request);
            
                if ($form->isSubmitted() && $form->isValid()) {
                  
                // Vérifier si les champs de mot de passe ont été remplis
                $plainPassword = $form->get('password')->getData();
                $confirmPassword = $form->get('confirmPassword')->getData(); 

                if (!empty($plainPassword)) {
                    // Vérifier si les deux champs correspondent
                    if ($plainPassword !== $confirmPassword) {
                        $this->addFlash('danger', 'Les mots de passe ne correspondent pas.');
                        return $this->redirectToRoute('app_edit_profile'); // Redirection immédiate
                    }
                
                    // Si les mots de passe correspondent, on les hash et on les enregistre
                    $hashedPassword = $userPasswordHasher->hashPassword($user, $plainPassword);
                    $user->setPassword($hashedPassword);
                }
              
                $imageFile = $form->get('image')->getData();
                dump($imageFile->getPathname()); // Affiche le chemin du fichier
                if ($imageFile) {
                    // Vérifie si le fichier existe et est lisible
                    if (!file_exists($imageFile->getPathname()) || !is_readable($imageFile->getPathname())) {
                        $this->addFlash('error', 'Le fichier sélectionné est invalide ou corrompu.');
                        return $this->redirectToRoute('app_edit_profile');
                    }
           
                try {
                    // Uploader l'image vers Cloudinary
                    $imageUrl = $uploader->upload($imageFile);
                    $user->setImage($imageUrl); // Enregistrer l'URL de l'image dans la base de données
                } catch (\Exception $e) {
                    $activityLogger->log('Échec de l’upload de l’image',$user);
        
                    $this->addFlash('error', 'Erreur lors de l’upload de l’image : ' . $e->getMessage());
                    return $this->redirectToRoute('app_register'); // Rediriger vers le formulaire en cas d'erreur
                }
                }
                
                $entityManager->flush();
                $this->addFlash('success', 'Votre profil a été mis à jour.');
              
               $activityLogger->log('Modification du profil',$user);
                return $this->redirectToRoute('app_userprofile');
            }
        
            return $this->render('utilisateur/UserProfile.html.twig', [
                'UserDetail' => $user, 
                'form' => $form->createView(),
            ]);
        }        
        

 
}
