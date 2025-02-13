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
use Symfony\Component\Security\Core\Security;
use App\Entity\Candidature;
use App\Form\CandidatureType;




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
    //Offres d'emploi agriculteur
    #[Route('offre/emploi', name: 'app_offreEmploi')]
    public function offreEmploi(OffreEmploiRepository $offreEmploiRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $offre = new OffreEmploi();
        $offre->setDatePublication(new \DateTime());
        $offre->setStatus('actif');

        $form = $this->createForm(OffreEmploiType::class, $offre);
        $form->handleRequest($request);

        $modalOpen = false; // Par défaut, le modal ne s'ouvre pas

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($offre);
                $entityManager->flush();
                return $this->redirectToRoute('app_offreEmploi');
            }
            $modalOpen = true; // Si erreurs, on ouvre le modal
        }

        $offres = $offreEmploiRepository->findAll();

        return $this->render('offre_emploi/index.html.twig', [
            'offres' => $offres,
            'form' => $form->createView(),
            'modal_open' => $modalOpen, 
        ]);
    }

    //Offres d'emploi employé
    #[Route('offre/emploi/employe', name: 'app_offreEmploiEmploye')]
    public function offreEmploiEmploye(
        OffreEmploiRepository $offreEmploiRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {
        // Récupérer toutes les offres d'emploi
        $offres = $offreEmploiRepository->findAll();
    
        // Créer un tableau de formulaires pour chaque offre
        $formulaireOffres = [];
        $forms = [];
        foreach ($offres as $offre) {
            $candidature = new Candidature();
            $form = $this->createForm(CandidatureType::class, $candidature);
            $form->handleRequest($request);
            // Ajoutez la vue du formulaire pour chaque offre
            $formulaireOffres[$offre->getId()] = $form->createView();
            $forms[$offre->getId()] = $form;
        }
    
        // Gestion de la soumission du formulaire
        if ($request->isMethod('POST')) {
            $offreId = $request->request->get('offre_id');
            $offre = $offreEmploiRepository->find($offreId);
    
            // Récupérer le formulaire de l'offre spécifique
            $form = $forms[$offreId];
    
            if ($form->isSubmitted() && $form->isValid()) {
                // Processus de la candidature
                $candidature = $form->getData();
                $candidature->setDateCandidature(new \DateTime());
                $candidature->setEtat('en attente');
                $candidature->setOffre($offre);
    
                $entityManager->persist($candidature);
                $entityManager->flush();
    
                $this->addFlash('success', 'Votre candidature a été soumise avec succès !');
                return $this->redirectToRoute('app_offreEmploiEmploye');
            }
        }
    
        return $this->render('offre_emploi/indexEmploye.html.twig', [
            'offres' => $offres,
            'formulaireOffres' => $formulaireOffres, // Passez les vues du formulaire
        ]);
    }
    
    
    
}
