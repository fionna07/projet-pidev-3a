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
use App\Entity\Candidature;



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
    //Modifier offre d'emploi par Agriculteur
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

        return $this->redirectToRoute('app_offre_emploi');
    }
    //Suppression d'une offre d'emploi par un Agriculteur
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

        return $this->redirectToRoute('app_offre_emploi');
    }
    //Suppression d'une offre d'emploi par l'Admin
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

        return $this->redirectToRoute('app_offre_emploi_back');
    }
    //Modification d'une offre d'emploi par l'Admin
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

        // Trouver toutes les candidatures de l'utilisateur connecté
        $candidatures = $candidatureRepository->findBy(['employe' => $user]);

        return $this->render('offre_emploi/mes_candidatures.html.twig', [
            'candidatures' => $candidatures,
        ]);
    }

    //Voir les détails d'une offre à partir des tableau des candidatures
    #[Route('/offre/{offreId}', name: 'app_details_offre')]
    public function detailsOffre($offreId, OffreEmploiRepository $offreRepository): Response
    {
        // Récupérer l'offre par son ID
        $offre = $offreRepository->find($offreId);

        // Si l'offre n'existe pas, rediriger ou afficher une erreur
        if (!$offre) {
            throw $this->createNotFoundException('Offre non trouvée');
        }
        return $this->render('offre_emploi/details.html.twig', [
            'offre' => $offre,
        ]);
    }
    //Annulation de candidature par un Employé 
    #[Route('/candidature/annuler/{candidatureId}', name: 'app_annuler_candidature', methods: ['POST'])]
    public function annulerCandidature($candidatureId, EntityManagerInterface $entityManager, CandidatureRepository $candidatureRepository): Response
    {
        // Récupérer la candidature en fonction de l'ID
        $candidature = $candidatureRepository->find($candidatureId);

        // Si la candidature n'existe pas, afficher une erreur
        if (!$candidature) {
            throw $this->createNotFoundException('Candidature non trouvée');
        }

        // Si la candidature est liée à une offre, la supprimer également
        if ($candidature->getOffre()) {
            // Supprimer la candidature liée à l'offre si nécessaire
            $entityManager->remove($candidature);
            $entityManager->flush();
        }

        // Ajouter un message de succès
        $this->addFlash('success', 'Votre candidature a été annulée.');

        return $this->redirectToRoute('app_mesCandidatures');
    }

    // Modification de candidature par un Employé (compétences)
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

        // Vérifier si l'état de la candidature a été modifié en "acceptée"
        if ($candidature->getEtat() === 'acceptée') {
            // Récupérer l'offre associée à cette candidature
            $offre = $candidature->getOffre();

            if ($offre) {
                // Vérifier si le nombre de postes est supérieur à 0 avant de décrémenter
                if ($offre->getNombrePostes() > 0) {
                    // Décrémenter le nombre de postes de l'offre
                    $offre->setNombrePostes($offre->getNombrePostes() - 1);
                    $entityManager->persist($offre);  
                }
            }
        }

        // Sauvegarder la candidature modifiée
        $entityManager->persist($candidature);
        $entityManager->flush();

        return $this->redirectToRoute('app_mesCandidatures');
    }

    //Lister les candidatures d'une offre d'emploi par l'Admin
    #[Route('/offre/{id}/candidatures', name: 'offre_candidatures_back')]
    public function offreCandidaturesBack(
        int $id,
        OffreEmploiRepository $offreEmploiRepository,
        CandidatureRepository $candidatureRepository
    ): Response {
        // Récupérer l'offre d'emploi par son ID
        $offre = $offreEmploiRepository->find($id);

        // Vérifier si l'offre existe
        if (!$offre) {
            throw $this->createNotFoundException("L'offre avec l'ID $id n'existe pas.");
        }

        // Récupérer les candidatures associées à cette offre
        $candidatures = $candidatureRepository->findBy(['offre' => $offre]);

        return $this->render('offre_emploi/listCandidaturesBack.html.twig', [
            'offre' => $offre,
            'candidatures' => $candidatures,
        ]);
    }
    //Supprimer candiature par l'Admin
    #[Route('/candidature/{id}/supprimer', name: 'candidature_supprimer_back', methods: ['POST'])]
    public function supprimerCandidatureBack(
        int $id,
        CandidatureRepository $candidatureRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupérer la candidature par son ID
        $candidature = $candidatureRepository->find($id);

        // Vérifier si la candidature existe
        if (!$candidature) {
            throw $this->createNotFoundException("La candidature avec l'ID $id n'existe pas.");
        }

        // Supprimer la candidature
        $entityManager->remove($candidature);
        $entityManager->flush();

        // Message flash de succès
        $this->addFlash('success', 'La candidature a été supprimée avec succès.');

        return $this->redirectToRoute('offre_candidatures_back', ['id' => $candidature->getOffre()->getId()]);
    }
    //Modifier l'état d'une candidature (accepté,refusée) par l'Admin
    #[Route('/candidature/{id}/modifier-etat', name: 'candidature_modifier_etat', methods: ['POST'])]
    public function modifierEtatCandidature(
        int $id,
        Request $request,
        CandidatureRepository $candidatureRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupérer la candidature par son ID
        $candidature = $candidatureRepository->find($id);

        // Vérifier si la candidature existe
        if (!$candidature) {
            throw $this->createNotFoundException("La candidature avec l'ID $id n'existe pas.");
        }

        // Récupérer le nouvel état depuis la requête
        $nouvelEtat = $request->request->get('etat');


        // Mettre à jour l'état de la candidature
        $candidature->setEtat($nouvelEtat);

        // Si l'état est "Acceptée", décrémenter le nombre de postes de l'offre associée
        if ($nouvelEtat === 'Acceptée') {
            $offre = $candidature->getOffre(); // Récupérer l'offre associée

            if ($offre) {
                // Vérifier si le nombre de postes est supérieur à 0 avant de décrémenter
                if ($offre->getNombrePostes() > 0) {
                    // Décrémenter le nombre de postes
                    $offre->setNombrePostes($offre->getNombrePostes() - 1);
                    $entityManager->persist($offre); 
                } else {
                    $this->addFlash('error', "Il n'y a plus de postes disponibles.");
                    return $this->redirectToRoute('offre_candidatures_back', ['id' => $candidature->getOffre()->getId()]);
                }
            }
        }

        // Sauvegarder les modifications de la candidature
        $entityManager->persist($candidature);
        $entityManager->flush();

        // Message flash de succès
        $this->addFlash('success', "L'état de la candidature a été mis à jour avec succès.");

        // Rediriger vers la liste des candidatures pour l'offre
        return $this->redirectToRoute('offre_candidatures_back', ['id' => $candidature->getOffre()->getId()]);
    }
    //Modifier le statut d'une offre par Agriculteur
    #[Route("/candidature/update/{id}", name: "candidature_update", methods: ["POST"])]
    public function update(Request $request, Candidature $candidature, EntityManagerInterface $entityManager): Response
    {
        // Vérifier si le champ 'statut' est présent dans la requête
        $newStatus = $request->request->get('statut');

        if ($newStatus === null) {
            $this->addFlash('error', 'Le statut est requis.');
            return $this->redirectToRoute('liste_candidatures', ['_fragment' => 'section-candidatures']);
        }

        // Récupérer l'offre liée à la candidature
        $offre = $candidature->getOffre();

        // Vérifier si le statut passe à "acceptée"
        if ($newStatus === 'acceptée' && $offre->getNombrePostes() > 0) {
            // Décrémenter le nombre de postes disponibles
            $offre->setNombrePostes($offre->getNombrePostes() - 1);
        } elseif ($newStatus === 'acceptée' && $offre->getNombrePostes() <= 0) {
            $this->addFlash('error', 'Impossible d\'accepter cette candidature, il n\'y a plus de postes disponibles.');
            return $this->redirectToRoute('liste_candidatures', ['_fragment' => 'section-candidatures']);
        }

        // Mettre à jour l'état de la candidature
        $candidature->setEtat($newStatus);

        // Enregistrer les changements en base de données
        $entityManager->flush();

        $this->addFlash('success', 'Statut mis à jour avec succès !');

        return $this->redirectToRoute('app_candidatures');
    }    
  
}
