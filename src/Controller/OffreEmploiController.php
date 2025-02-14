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
use App\Repository\CandidatureRepository;
use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Security;


class OffreEmploiController extends AbstractController
{
    #[Route('/offre/emploi', name: 'app_offre_emploi')]
    public function index(): Response
    {
        return $this->render('offre_emploi/index.html.twig', [
            'controller_name' => 'OffreEmploiController',
        ]);
    }

    #[Route('/back/offre', name: 'app_offre_emploi_back')]
    public function indexBack(): Response
    {
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

        // Ajouter un message flash de succès
        $this->addFlash('success', 'L\'offre d\'emploi a été mise à jour avec succès.');

        // Redirection
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

        // Message flash de succès
        $this->addFlash('success', 'L\'offre d\'emploi a été supprimée avec succès.');

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

        // Message flash de succès
        $this->addFlash('success', 'L\'offre d\'emploi a été supprimée de l\'administration.');

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

        // Message flash de succès
        $this->addFlash('success', 'L\'offre d\'emploi a été mise à jour avec succès.');

        return $this->redirectToRoute('app_offre_emploi_back');
    }

    //Affichage des candidatures pour un user connecté
    
    #[Route('/mes-candidatures', name: 'app_mesCandidatures')]
    public function mesCandidatures(CandidatureRepository $candidatureRepository, Security $security): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();

        // Vérifier si un utilisateur est connecté
        if (!$user) {
            // Gérer le cas où l'utilisateur n'est pas connecté, par exemple, rediriger vers la page de connexion
            $this->addFlash('error', 'Vous devez être connecté pour voir vos candidatures.');
            return $this->redirectToRoute('app_login'); // Change 'app_login' par la route appropriée pour la connexion
        }

        // Trouver toutes les candidatures de l'utilisateur connecté
        $candidatures = $candidatureRepository->findBy(['employe' => $user]);

        // Retourner les candidatures dans la vue
        return $this->render('offre_emploi/mes_candidatures.html.twig', [
            'candidatures' => $candidatures,
        ]);
    }

    //détails offre
    #[Route('/offre/{offreId}', name: 'app_details_offre')]
    public function detailsOffre($offreId, OffreEmploiRepository $offreRepository): Response
    {
        // Récupérer l'offre par son ID
        $offre = $offreRepository->find($offreId);

        // Si l'offre n'existe pas, rediriger ou afficher une erreur
        if (!$offre) {
            throw $this->createNotFoundException('Offre non trouvée');
        }

        // Renvoyer la vue avec les détails de l'offre
        return $this->render('offre_emploi/details.html.twig', [
            'offre' => $offre,
        ]);
    }
    //Annuler candidature
    #[Route('/candidature/annuler/{candidatureId}', name: 'app_annuler_candidature', methods: ['POST'])]
    public function annulerCandidature($candidatureId, EntityManagerInterface $entityManager, CandidatureRepository $candidatureRepository): Response
    {
        // Récupérer la candidature en fonction de l'ID
        $candidature = $candidatureRepository->find($candidatureId);

        // Si la candidature n'existe pas, afficher une erreur
        if (!$candidature) {
            throw $this->createNotFoundException('Candidature non trouvée');
        }

        // Annuler la candidature (par exemple, la supprimer ou mettre à jour son état)
        $entityManager->remove($candidature);
        $entityManager->flush();

        // Ajouter un message de succès
        $this->addFlash('success', 'Votre candidature a été annulée.');

        // Rediriger vers la page des candidatures
        return $this->redirectToRoute('app_mesCandidatures');
    }
    //Modifier Candidature
    #[Route('/candidature/{candidatureId}/modifier-compétences', name: 'app_modifier_competence', methods: ['POST'])]
    public function modifierCompetences(Request $request, CandidatureRepository $candidatureRepository, EntityManagerInterface $entityManager, $candidatureId): Response
    {
        // Récupérer la candidature avec l'ID passé en paramètre
        $candidature = $candidatureRepository->find($candidatureId);

        if (!$candidature) {
            throw $this->createNotFoundException('Candidature non trouvée');
        }

        // Récupérer les compétences modifiées depuis la requête
        $competences = $request->request->get('competences');

        // Mettre à jour les compétences de la candidature
        $candidature->setCompetences($competences);

        // Sauvegarder la candidature modifiée
        $entityManager->persist($candidature);
        $entityManager->flush();

        // Rediriger vers la page des candidatures
        return $this->redirectToRoute('app_mesCandidatures');
    }



    

    
}
