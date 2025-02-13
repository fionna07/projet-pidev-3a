<?php

namespace App\Controller;
//login
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
//regsiter
use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Security\UtilisateurAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
//email
use App\Service\EmailService;
class SecurityController extends AbstractController
{
    private $emailService;
    private $security;
    // Injecter le service EmailService
    public function __construct(EmailService $emailService,Security $security)
    {
        $this->emailService = $emailService;
        $this->security = $security;
        
    }
    #[Route('/back/pages', name: 'admin_dashboard')]
    public function index(): Response
    {
        return $this->render('back_pages/index.html.twig', [
            'controller_name' => 'BackPagesController',
        ]);
    }
    #[Route(path: '/login', name: 'app_login')]
public function login(AuthenticationUtils $authenticationUtils): Response
{
    $user = $this->getUser(); 

    // Vérifier si l'utilisateur est déjà connecté
    if ($user instanceof Utilisateur) {
        // Vérifier le rôle de l'utilisateur
        if (in_array('ROLE_USER', $user->getRoles())) {
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

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
    #[Route('/forgetPassword', name: 'app_forgetPassword')]
    public function forgetPassword(): Response
    {
        return $this->render('security/forget-password.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }
  
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new Utilisateur();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
    
            // Assigner le rôle sélectionné
            $user->setRoles($form->get('roles')->getData());
            dump($form->get('roles')->getData()); // Vérifie si c'est bien un tableau

              /** @var UploadedFile $imageFile */
        $imageFile = $form->get('image')->getData();

        if ($imageFile) {
            $newFilename = uniqid().'.'.$imageFile->guessExtension();
            $imageFile->move(
                $this->getParameter('uploads_directory'), // Assure-toi que ce paramètre est défini
                $newFilename
            );
            $user->setImage($newFilename);
        }

    
            // Encoder le mot de passe
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setDateCreation(new \DateTime());
            $user->setStatus('en attente de validation'); // Status initial lors de l'inscription
            $user->setIsVerified(false);

            // Générer un token unique
            $token = bin2hex(random_bytes(32));  // Génère un token de 64 caractères
            $user->setConfirmationToken($token); // Assigner le token à l'utilisateur

            $entityManager->persist($user);
            $entityManager->flush();
            /*
          // Générer un lien de confirmation avec le token
          $confirmationLink = $this->generateUrl('app_verify_email', [
            'token' => $token,
        ], 0);
       $this->addFlash('success',"veuillez verifier votre mail pour activer ton compte");
        // Envoi de l'email de confirmation avec le lien
        //$this->emailService->sendConfirmationEmail($user->getEmail(), $confirmationLink);*/

        // Connexion automatique après l'inscription
        return $this->redirectToRoute('app_login'); // ou une autre page après l'inscription
    }

    return $this->render('security/register.html.twig', [
        'registrationForm' => $form,
    ]);
}
    

 // Route pour vérifier l'email
 #[Route('/verify/email/{token}', name: 'app_verify_email')]
 public function verifyUserEmail(string $token, EntityManagerInterface $entityManager): Response
 {
     // Vérifier si un utilisateur avec ce token existe
     $user = $entityManager->getRepository(Utilisateur::class)->findOneBy(['confirmationToken' => $token]);

     if (!$user) {
         // Si l'utilisateur n'existe pas ou le token est invalide
         $this->addFlash('error', 'Token de confirmation invalide.');
         return $this->redirectToRoute('app_register');
     }

     // Valider l'utilisateur
     $user->setIsVerified(true);
     $user->setStatus('activé');
     $user->setConfirmationToken(null); // Supprimer le token après validation

     $entityManager->flush(); // Sauvegarder les changements

     // Afficher un message de succès
     $this->addFlash('success', 'Votre email a bien été vérifié. Vous pouvez maintenant vous connecter.');

     return $this->redirectToRoute('app_login');
 }
}


