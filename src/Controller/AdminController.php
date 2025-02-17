<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\UserEditType;
use App\Form\UserAddType;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\ImageUploader;
use Symfony\Component\Security\Http\Attribute\IsGranted;
final class AdminController extends AbstractController{

    #[Route('/admin/profile', name: 'app_profile_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function profile(): Response
    {
       // Simuler des données (remplace ça par une vraie requête à la base de données)
         $activityData = [5, 10, 3, 7, 15, 9, 12]; // Actions par jour de la semaine

     return $this->render('admin/profileAdmin.html.twig', [
        'user' => $this->getUser(),
        'activityData' => json_encode($activityData) // Convertir les données en JSON
    ]);
    }
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('back_pages/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

        #[Route(path: '/login', name: 'app_login')]
        public function login(AuthenticationUtils $authenticationUtils): Response
        {
        $user = $this->getUser(); 

        // Vérifier si l'utilisateur est déjà connecté
        if ($user instanceof Utilisateur) {
            // Vérifier le rôle de l'utilisateur
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->redirectToRoute('admin_dashboard');
            } else {
                return $this->redirectToRoute('app_front');
            }
        }

        // Récupérer l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();
        // Récupérer le dernier nom d'utilisateur saisi
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername, 
            'error' => $error
        ]);
        }
    #[Route('/admin/users', name: 'admin_users')]
        public function listUsers(EntityManagerInterface $entityManager): Response
        {
            $users = $entityManager->getRepository(Utilisateur::class)->findAll();

            return $this->render('admin/users.html.twig', [
                'users' => $users,
            ]);
        }
        #[Route('/admin/user/add', name: 'user_add')]
        public function addUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, ImageUploader $uploader): Response
        {
            $user = new Utilisateur();
        $form = $this->createForm(UserAddType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('password')->getData();
    
            // Assigner le rôle sélectionné
            $user->setRoles($form->get('roles')->getData());
            dump($form->get('roles')->getData()); // Vérifie si c'est bien un tableau

        // Gérer l'upload d'image
   
        $imageFile = $form->get('image')->getData();
        dump($imageFile->getPathname()); // Affiche le chemin du fichier
        if ($imageFile) {
            // Vérifie si le fichier existe et est lisible
            if (!file_exists($imageFile->getPathname()) || !is_readable($imageFile->getPathname())) {
                $this->addFlash('error', 'Le fichier sélectionné est invalide ou corrompu.');
                return $this->redirectToRoute('app_register');
            }
   
       try {
           // Uploader l'image vers Cloudinary
           $imageUrl = $uploader->upload($imageFile);
           $user->setImage($imageUrl); // Enregistrer l'URL de l'image dans la base de données
       } catch (\Exception $e) {
           $this->addFlash('error', 'Erreur lors de l’upload de l’image : ' . $e->getMessage());
           return $this->redirectToRoute('user_add');
        }
   }
    
            // Encoder le mot de passe
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $user->setDateCreation(new \DateTime());
            $user->setStatus('en attente de validation'); // Status initial lors de l'inscription
            $user->setIsVerified(false);

            // Générer un token unique
            $token = bin2hex(random_bytes(32));  // Génère un token de 64 caractères
            $user->setConfirmationToken($token); // Assigner le token à l'utilisateur

            $em->persist($user);
            $em->flush();
            
            
          // Générer un lien de confirmation avec le token
         /* $confirmationLink = $this->generateUrl('app_verify_email', [
            'token' => $token,
        ], 0);
       $this->addFlash('success',"veuillez verifier votre mail pour activer ton compte");
        // Envoi de l'email de confirmation avec le lien
        $this->emailService->sendConfirmationEmail($user->getEmail(), $confirmationLink);*/
 // Ajouter un message de succès avant redirection
 $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');

        // Connexion automatique après l'inscription
        return $this->redirectToRoute('admin_users'); // ou une autre page après l'inscription
    }

    return $this->render('admin/add_user.html.twig', [
        'form' => $form,
    ]);
        }
        
    #[Route('/admin/user/{id}', name: 'user_show', requirements: ['id' => '\d+'])]
    public function show(Utilisateur $user): Response
    {
        return $this->render('admin/user_show.html.twig', ['utilisateur' => $user]);
    }

    #[Route('/admin/user/{id}/edit', name: 'user_edit', requirements: ['id' => '\d+'])]
    public function edit(Utilisateur $user, Request $request, EntityManagerInterface $em, ImageUploader $uploader): Response
    {
        // Création du formulaire
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);
    
        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            // Vérifier si une nouvelle image a été soumise
            if ($imageFile !== null) { 
                if (!file_exists($imageFile->getPathname()) || !is_readable($imageFile->getPathname())) {
                    $this->addFlash('error', 'Le fichier sélectionné est invalide ou corrompu.');
                    return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
                }
            
                try {
                    // Uploader la nouvelle image
                    $imageUrl = $uploader->upload($imageFile);
                    $user->setImage($imageUrl); // Mettre à jour l'URL de l'image
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l’upload de l’image : ' . $e->getMessage());
                    return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
                }
            }
            
            $em->flush(); // Sauvegarde des modifications dans la base de données
    
            // Redirection vers la liste des utilisateurs (à adapter selon ton projet)
            return $this->redirectToRoute('admin_users');
        }
    
        // Affichage du formulaire dans le template Twig
        return $this->render('admin/user_edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
    

    #[Route('/admin/user/{id}/delete', name: 'user_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Utilisateur $user, EntityManagerInterface $em, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $token = new CsrfToken('delete' . $user->getId(), $request->request->get('_token'));
    
        if (!$csrfTokenManager->isTokenValid($token)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }
    
        $em->remove($user);
        $em->flush();
    
        $this->addFlash('success', 'Utilisateur supprimé avec succès.');
    
        return $this->redirectToRoute('admin_users'); 
    }
    


    #[Route('/admin/user/{id}/disable', name: 'user_disable', requirements: ['id' => '\d+'])]
    public function disable(Utilisateur $user, EntityManagerInterface $em): Response
    {
        $user->setStatus('désactivé');
        $em->flush();
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/admin/user/{id}/enable', name: 'user_enable', requirements: ['id' => '\d+'])]
    public function enable(Utilisateur $user, EntityManagerInterface $em): Response
    {
        $user->setStatus('actif');
        $em->flush();
        return $this->redirectToRoute('admin_users'); // Assurez-vous que 'user_list' existe
    }

}
