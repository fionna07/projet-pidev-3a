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


class BackPagesController extends AbstractController
{
    #[Route('/back/pages', name: 'app_back_pages')]
    public function index(): Response
    {
        return $this->render('back_pages/index.html.twig', [
            'controller_name' => 'BackPagesController',
        ]);
    }
    #[Route('/back/offre', name: 'app_back_offre')]
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
            return $this->redirectToRoute('app_back_offre');
        }

        // Récupérer toutes les offres
        $offres = $offreEmploiRepository->findAll();

        // Afficher le template
        return $this->render('offre_emploi/indexBack.html.twig', [
            'offres' => $offres,
            'form' => $form->createView(), // Passer le formulaire au template
        ]);
    }
}
