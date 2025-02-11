<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
class BackPagesController extends AbstractController
{
    #[Route('/back/pages', name: 'app_back_pages')]
    public function index(): Response
    {
        return $this->render('back_pages/index.html.twig', [
            'controller_name' => 'BackPagesController',
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
}
