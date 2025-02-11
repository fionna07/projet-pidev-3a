<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request; 
use App\Entity\OffreEmploi;
use App\Form\OffreEmploiType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OffreEmploiRepository;

class OffreEmploiController extends AbstractController
{
    #[Route('/offre/emploi', name: 'app_offre_emploi')]
    public function index(): Response
    {
        // Pass a string instead of an undefined constant
        return $this->render('offre_emploi/index.html.twig', [
            'controller_name' => 'OffreEmploiController',
        ]);
    }

    #[Route('/offre/emploi/admin', name: 'app_offre_emploi_back')]
    public function indexBack(): Response
    {
        // Pass a string instead of an undefined constant
        return $this->render('offre_emploi/indexBack.html.twig', [
            'controller_name' => 'OffreEmploiController',
        ]);
    }
   
    #[Route("/offre/edit/{id}", name: "offre_edit", methods: ["POST"])]
    public function edit(
        int $id,
        Request $request,
        OffreEmploiRepository $offreEmploiRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupération de l'offre d'emploi
        $offre = $offreEmploiRepository->find($id);
        if (!$offre) {
            throw $this->createNotFoundException("L'offre avec l'ID $id n'existe pas.");
        }

        // Mise à jour des champs
        $offre->setTitre($request->request->get('titre'));
        $offre->setDescription($request->request->get('description'));
        $offre->setNombrePostes((int) $request->request->get('nombrePostes'));

        // Vérifier si les dates de début et de fin sont renseignées
        if ($request->request->get('dateDebut')) {
            $offre->setDateDebut(new \DateTime($request->request->get('dateDebut')));
        }
        if ($request->request->get('dateFinEstimee')) {
            $offre->setDateFinEstimee(new \DateTime($request->request->get('dateFinEstimee')));
        }

        $offre->setCompetencesRequises($request->request->get('competencesRequises'));
        $offre->setSalaire((float) $request->request->get('salaire'));
        $offre->setLocalisation($request->request->get('localisation'));

        // Mise à jour du statut (Actif ou Inactif)
        $offre->setStatus($request->request->get('status'));

        // Sauvegarde
        $entityManager->persist($offre);
        $entityManager->flush();

        return $this->redirectToRoute('app_offre_emploi');
    }

    #[Route("/offre/{id}/supprimer", name: "offre_supprimer", methods: ["POST"])]
    public function supprimerOffre(
        int $id, 
        OffreEmploiRepository $offreEmploiRepository, 
        EntityManagerInterface $entityManager
    ): Response {
        // Récupération de l'offre d'emploi
        $offre = $offreEmploiRepository->find($id);
        if (!$offre) {
            throw $this->createNotFoundException("L'offre avec l'ID $id n'existe pas.");
        }

        // Suppression de l'offre
        $entityManager->remove($offre);
        $entityManager->flush();

        // Rediriger vers la liste des offres après suppression
        return $this->redirectToRoute('app_offre_emploi');
    }
    #[Route("/offre/{id}/supprimer/admin", name: "offre_supprimer_back", methods: ["POST"])]
    public function supprimerOffreBack(
        int $id, 
        OffreEmploiRepository $offreEmploiRepository, 
        EntityManagerInterface $entityManager
    ): Response {
        // Récupération de l'offre d'emploi
        $offre = $offreEmploiRepository->find($id);
        if (!$offre) {
            throw $this->createNotFoundException("L'offre avec l'ID $id n'existe pas.");
        }

        // Suppression de l'offre
        $entityManager->remove($offre);
        $entityManager->flush();

        // Rediriger vers la liste des offres après suppression
        return $this->redirectToRoute('app_offre_emploi_back');
    }
    #[Route("/offre/edit/admin/{id}", name: "offre_edit_back", methods: ["POST"])]
    public function editOffre(
        int $id,
        Request $request,
        OffreEmploiRepository $offreEmploiRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupération de l'offre d'emploi
        $offre = $offreEmploiRepository->find($id);
        if (!$offre) {
            throw $this->createNotFoundException("L'offre avec l'ID $id n'existe pas.");
        }

        // Mise à jour des champs
        $offre->setTitre($request->request->get('titre'));
        $offre->setDescription($request->request->get('description'));
        $offre->setNombrePostes((int) $request->request->get('nombrePostes'));

        // Vérifier si les dates de début et de fin sont renseignées
        if ($request->request->get('dateDebut')) {
            $offre->setDateDebut(new \DateTime($request->request->get('dateDebut')));
        }
        if ($request->request->get('dateFinEstimee')) {
            $offre->setDateFinEstimee(new \DateTime($request->request->get('dateFinEstimee')));
        }

        $offre->setCompetencesRequises($request->request->get('competencesRequises'));
        $offre->setSalaire((float) $request->request->get('salaire'));
        $offre->setLocalisation($request->request->get('localisation'));

        // Mise à jour du statut (Actif ou Inactif)
        $offre->setStatus($request->request->get('status'));

        // Sauvegarde
        $entityManager->persist($offre);
        $entityManager->flush();

        return $this->redirectToRoute('app_offre_emploi_back');
    }

    
    

    
}
