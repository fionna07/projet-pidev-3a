<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UtilisateurController extends AbstractController
{
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
