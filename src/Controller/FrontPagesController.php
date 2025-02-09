<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\OffreEmploiRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\OffreEmploi;
use App\Form\OffreEmploiType;




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
    public function offreEmploi(OffreEmploiRepository $offreEmploiRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Créer une nouvelle offre
        $offre = new OffreEmploi();

        // Définir la date de publication comme la date actuelle
        $offre->setDatePublication(new \DateTime());

        // Définir le statut comme "actif" par défaut
        $offre->setStatus('actif');

        // Créer le formulaire
        $form = $this->createForm(OffreEmploiType::class, $offre);

        // Gérer la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer l'offre
            $entityManager->persist($offre);
            $entityManager->flush();

            // Rediriger vers la page des offres
            return $this->redirectToRoute('app_offreEmploi');
        }

        // Récupérer toutes les offres
        $offres = $offreEmploiRepository->findAll();

        // Afficher le template
        return $this->render('offre_emploi/index.html.twig', [
            'offres' => $offres,
            'form' => $form->createView(), // Passer le formulaire au template
        ]);
    }
    
}
