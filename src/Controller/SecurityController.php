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
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
//email
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use App\Service\EmailService;
//ilage
use App\Service\ImageUploader;
//activity
use App\Entity\Activites;
use App\Service\ActivityLoggerService;
class SecurityController extends AbstractController
{
    private $emailService;
    private $security;
    private ActivityLoggerService $activityLogger;

  
    public function __construct(EmailService $emailService,Security $security,ActivityLoggerService $activityLogger)
    {
        $this->emailService = $emailService;
        $this->security = $security;
        $this->activityLogger = $activityLogger;
        

    }
   /* #[Route('/test-session', name: 'test_session')]
    public function testSession(Request $request): Response
    {
        $session = $request->getSession();

        // Vérifier si la session fonctionne
        $session->set('test', 'Session active');
        return new Response("Valeur en session : " . $session->get('test'));
    }*/
    #[Route('/back/pages', name: 'admin_dashboard')]
    public function index(): Response
    {
        return $this->render('back_pages/index.html.twig', [
            'controller_name' => 'BackPagesController',
        ]);
    }
    // src/Controller/SecurityController.php



   
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, ActivityLoggerService $activityLogger): Response
    {
        $user = $this->getUser(); 
    
        if ($user instanceof Utilisateur) {
            $activityLogger->log('Connexion réussie',$user);
          
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
        if ($error) {
            $activityLogger->log('Échec de connexion',$user);
        }
    
        // Retourner la vue avec la dernière valeur d'email (username) et l'erreur
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername, // Utiliser 'last_username' pour pré-remplir l'email
            'error' => $error
        ]);
    }
    

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    

    }

    /*#[Route('/forgetPassword', name: 'app_forgetPassword')]
    public function forgetPassword(): Response
    {
        return $this->render('security/forget-password.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }*/
  
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager, ImageUploader $uploader,ActivityLoggerService $activityLogger, MailerInterface $mailer): Response
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
            $activityLogger->log('Échec de l’upload de l’image',$user);

            $this->addFlash('error', 'Erreur lors de l’upload de l’image : ' . $e->getMessage());
            return $this->redirectToRoute('app_register'); // Rediriger vers le formulaire en cas d'erreur
        }
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
            $activityLogger->log('Nouvelle inscription',$user);
            //envoi mail
              // Générer un token unique pour l'email de confirmation
              $token = bin2hex(random_bytes(32));
              $user->setConfirmationToken($token);
  
              // Sauvegarder l'utilisateur en base de données
              $entityManager->persist($user);
              $entityManager->flush();
  
              $transport = Transport::fromDsn('smtp://benharbfarah85@gmail.com:usvjuzoqaluwufif@smtp.gmail.com:587?encryption=tls&auth_mode=login');
              $mailer = new Mailer($transport);
  
  
              $confirmationLink = $this->generateUrl('app_verify_email', ['token' => $token], 0);
  
              $emailContent = "
              Bonjour,
              <br><br>
              Nous avons bien reçu votre inscription. Veuillez cliquer sur le lien ci-dessous pour confirmer votre inscription :
              <br><br>
              <a href=\"$confirmationLink\">Confirmer mon inscription</a>
              <br><br>
              Cordialement,<br>
              L'équipe de votre application
             ";
  
              try {
                  $email = (new Email())
                      ->from(new Address('wefarmApplicaiton@gmail.com', 'Wefarm Support'))
                      ->to($user->getEmail())
                      ->subject('Confirmation de votre inscription')
                      ->html($emailContent);
  
                  $mailer->send($email);
  
                  $this->addFlash('success', 'Email envoyé avec succès.');
              } catch (\Exception $e) {
                  $this->addFlash('error', 'Erreur lors de l’envoi de l’email: ' . $e->getMessage());
              }
  
              // Ajouter un message de succès
              $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');

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
     $user->setConfirmationToken(null); // Supprimer le token après validation

     $entityManager->flush(); // Sauvegarder les changements

     // Afficher un message de succès
     $this->addFlash('success', 'Votre email a bien été vérifié. Vous pouvez maintenant vous connecter.');

     return $this->redirectToRoute('app_login');
 }
}


