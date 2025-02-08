<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request; 
use App\Entity\OffreEmploi;
use App\Form\OffreEmploiType;
use Doctrine\ORM\EntityManagerInterface;

class OffreEmploiController extends AbstractController
{
    #[Route('/offre/emploi', name: 'app_offre_emploi')]
    public function index(OffreEmploiRepository $offreEmploiRepository): Response
    {
        // Récupérer toutes les offres d'emploi depuis le repository
        $offres = $offreEmploiRepository->findAllOffres();

        // Renvoyer les données à la vue
        return $this->render('offre_emploi/index.html.twig', [
            'offres' => $offres,
        ]);
    }
    #[Route('/offre/emploi/ajouter', name: 'app_offre_emploi_ajouter')]
    public function ajouter(Request $request, EntityManagerInterface $entityManager): Response
    {
        $offreEmploi = new OffreEmploi();

        // Créez le formulaire
        $form = $this->createForm(OffreEmploiType::class, $offreEmploi);

        // Gérer la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Définir les valeurs par défaut
            $offreEmploi->setStatus('actif');
            $offreEmploi->setDatePublication(new \DateTime());

            // Sauvegarder dans la base de données
            $entityManager->persist($offreEmploi);
            $entityManager->flush();

            // Rediriger vers une autre page
            return $this->redirectToRoute('app_offre_emploi');
        }

        return $this->render('offre_emploi/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
}
