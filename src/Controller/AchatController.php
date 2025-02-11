<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AchatController extends AbstractController
{
    #[Route('/achat', name: 'app_achat')]
    public function index(): Response
    {
        return $this->render('achat/index.html.twig', [
            'controller_name' => 'AchatController',
        ]);
    }
}
