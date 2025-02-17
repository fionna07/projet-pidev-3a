<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BackPagesController extends AbstractController
{
    #[Route('/back/pages', name: 'app_back_pages')]
    public function index(): Response
    {
        return $this->render('back_pages/index.html.twig', [
            'controller_name' => 'BackPagesController',
        ]);
    }
}
