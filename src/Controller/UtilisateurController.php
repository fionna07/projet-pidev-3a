<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Utilisateur;
class UtilisateurController extends AbstractController
{
    #[Route('/create/agriculteur', name: 'create_agriculteur')]
    public function createAgriculteur(EntityManagerInterface $em): Response
    {
        // Création d'un nouvel utilisateur
        $user = new Utilisateur();
        $user->setFirstName('Agriculteur')
            ->setLastName('Ben Agriculteur')
            ->setEmail('agriculteur@example.com')
            ->setRole(['ROLE_AGRICULTEUR'])  // Assurez-vous que le rôle est configuré
            ->setPassword('password123')  // Vous devrez probablement utiliser un encodeur de mot de passe dans une application réelle
            ->setDateCreation(new \DateTime())  // Date de création actuelle
            ->setStatus('actif')  // Un statut valide
            ->setVerified(true)  // Utilisateur vérifié
            ->setImage('image_agriculteur.jpg')  // Image par défaut
            ->setAdresse('Adresse de l\'Agriculteur, Tunis');

        // Persist et flush pour enregistrer l'utilisateur
        $em->persist($user);
        $em->flush();

        return new Response('Agriculteur créé avec succès');
    }
    #[Route('/utilisateur', name: 'app_utilisateur')]
    public function index(): Response
    {
        return $this->render('utilisateur/index.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }
    #[Route('/utilisateur/register', name: 'app_register')]
    public function register(): Response
    {
        return $this->render('utilisateur/register.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }
    #[Route('/utilisateur/login', name: 'app_login')]
    public function login(): Response
    {
        return $this->render('utilisateur/login.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }
    #[Route('/utilisateur/forgetPassword', name: 'app_forgetPassword')]
    public function forgetPassword(): Response
    {
        return $this->render('utilisateur/forget-password.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }
 
}
