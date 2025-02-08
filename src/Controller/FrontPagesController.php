<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FrontPagesController extends AbstractController
{
    #[Route('/front/pages', name: 'app_front')]
    public function index(): Response
    {
        return $this->render('front_pages/index.html.twig', [
            'controller_name' => 'FrontPagesController',
        ]);
    }

    #[Route('/front/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('front_pages/contact.html.twig', [
            'controller_name' => 'FrontController',
        ]);
    }
    #[Route('/front/services', name: 'app_services')]
    public function services(): Response
    {
        return $this->render('front_pages/services.html.twig', [
            'controller_name' => 'FrontController',
        ]);
    }
    #[Route('/front/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('front_pages/about.html.twig', [
            'controller_name' => 'FrontController',
        ]);
    }
    #[Route('offre/emploi', name: 'app_offreEmploi')]
    public function offreEmploi(): Response
    {
        return $this->render('offre_emploi/index.html.twig', [
            'controller_name' => 'FrontController',
        ]);
    }
}
