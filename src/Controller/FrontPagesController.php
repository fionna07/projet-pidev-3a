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
use App\Entity\Utilisateur;
use App\Repository\CandidatureRepository;
use App\Repository\EvenementRepository;
use App\Entity\Evenement;
use App\Service\ActivityLoggerService;

class FrontPagesController extends AbstractController
{
    private ActivityLoggerService $activityLogger;
    public function __construct(ActivityLoggerService $activityLogger)
    {
       
        $this->activityLogger = $activityLogger;
        

    }
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
    //Offres d'emploi page Agriculteur
    #[Route('offre/emploi', name: 'app_offreEmploi')]
    public function offreEmploi(
        OffreEmploiRepository $offreEmploiRepository, 
        Request $request, 
        EntityManagerInterface $entityManager, 
        Security $security,
        ActivityLoggerService $activityLogger
    ): Response {
        $offre = new OffreEmploi();
        $offre->setDatePublication(new \DateTime());
        $offre->setStatus('actif');

        // Récupérer l'utilisateur connecté
        $user = $security->getUser();

        if ($user instanceof Utilisateur) {
            $offre->setUser($user); 
        }

        $form = $this->createForm(OffreEmploiType::class, $offre);
        $form->handleRequest($request);

        $modalOpen = false; // Par défaut, le modal ne s'ouvre pas

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($offre); // Sauvegarder l'offre
                $entityManager->flush(); // Valider les changements dans la base de données

                // Affichage du message de succès
                $this->addFlash('success', 'L\'offre d\'emploi a été ajoutée avec succès.');
                $activityLogger->log('Ajout d\'une offre',$user);

                return $this->redirectToRoute('app_offreEmploi'); 
            }

            $modalOpen = true; // Si des erreurs, on ouvre le modal
        }

        // Récupérer toutes les offres d'emploi
        $offres = $offreEmploiRepository->findAll();

        return $this->render('offre_emploi/index.html.twig', [
            'offres' => $offres,
            'form' => $form->createView(),
            'modal_open' => $modalOpen, 
        ]);
    }

    //Offre d'employé page Employé
    #[Route('offre/emploi/employe', name: 'app_offreEmploiEmploye')]
    public function offreEmploiEmploye(
        OffreEmploiRepository $offreEmploiRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security,
        CandidatureRepository $candidatureRepository 
    ): Response {
        // Récupérer toutes les offres d'emploi
        $offres = $offreEmploiRepository->findAll();

        // Créer un tableau de formulaires postuler pour chaque offre
        $formulaireOffres = [];
        $forms = [];
        $modalOpen = false; // Par défaut, le modal ne s'ouvre pas

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

            // Vérifier si l'utilisateur a déjà postulé à cette offre
            $user = $security->getUser();
            if ($user) {
                $existingCandidature = $candidatureRepository->findOneBy([
                    'offre' => $offre,
                    'employe' => $user,
                ]);
                if ($existingCandidature) {
                    // Si la candidature existe déjà, afficher un message d'erreur
                    $this->addFlash('error', 'Vous avez déjà postulé à cette offre.');
                    return $this->redirectToRoute('app_offreEmploiEmploye'); 
                }
            }

            // Récupérer le formulaire de l'offre spécifique
            $form = $forms[$offreId];

            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    // Processus de la candidature
                    $candidature = $form->getData();
                    $candidature->setDateCandidature(new \DateTime());
                    $candidature->setEtat('en attente');
                    $candidature->setOffre($offre);

                    // Associer l'utilisateur connecté à la candidature
                    if ($user) {
                        $candidature->setEmploye($user);
                    }

                    $entityManager->persist($candidature);
                    $entityManager->flush();

                    $this->addFlash('success', 'Votre candidature a été soumise avec succès !');
                    return $this->redirectToRoute('app_offreEmploiEmploye');
                } else {
                    $modalOpen = true; // Si erreurs, on ouvre le modal
                }
            }
        }

        return $this->render('offre_emploi/indexEmploye.html.twig', [
            'offres' => $offres,
            'formulaireOffres' => $formulaireOffres, 
            'modal_open' => $modalOpen, 
        ]);
    }
    //Affichage des candidatures pour les offres de l'agriculteur connecté
    #[Route('/candidatures', name: 'app_candidatures')]
    public function listCandidatures(CandidatureRepository $candidatureRepository): Response
    {
        // Récupérer toutes les candidatures
        $candidatures = $candidatureRepository->findAll();

        // Passer les candidatures à la vue Twig
        return $this->render('offre_emploi/listCandidatures.html.twig', [
            'candidatures' => $candidatures,
        ]);
    }
    //Evénement interface Agriculteur
    #[Route(name: 'app_events', methods: ['GET'])]
    public function events (EvenementRepository $evenementRepository): Response
    {
        return $this->render('events/index.html.twig', [
            'evenements' => $evenementRepository->findAll(),
        ]);
    }
    //Evénement interface Client
    #[Route('/event/client', name: 'app_events_client')]
    public function eventsClient (EvenementRepository $evenementRepository): Response
    {
        return $this->render('events/indexClient.html.twig', [
            'evenements' => $evenementRepository->findAll(),
        ]);
    }

    
}
