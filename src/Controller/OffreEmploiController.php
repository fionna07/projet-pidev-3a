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
use App\Service\MatchingService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Service\SmsService; 
use App\Service\EmailService;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use App\Service\TranslationService; 
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\ActivityLoggerService;
use App\Form\CandidatureType;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Symfony\Component\HttpFoundation\RequestStack;

class OffreEmploiController extends AbstractController
{
    private $matchingService;
    private $smsService;
    private TranslationService $translator;
    private $emailService;
    private ActivityLoggerService $activityLogger;
    private OffreEmploiRepository $offreEmploiRepository;


    public function __construct(OffreEmploiRepository $offreEmploiRepository,ActivityLoggerService $activityLogger,EmailService $emailService,MatchingService $matchingService,SmsService $smsService,TranslationService $translator)
    {
        $this->matchingService = $matchingService;
        $this->smsService = $smsService;
        $this->translator=$translator;
        $this->emailService=$emailService;
        $this->activityLogger = $activityLogger;
        $this->offreEmploiRepository = $offreEmploiRepository;

    }
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
    //Ajout d'une offre par agriculteur
    #[Route('/offre/emploi/ajouter', name: 'app_offreEmploi_ajouter', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security,ActivityLoggerService $activityLogger): Response
    {
        $offreEmploi = new OffreEmploi();
        
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();
        
        // Vérifier si un utilisateur est connecté
        if ($user) {
            // Associer l'utilisateur connecté à l'offre d'emploi
            $offreEmploi->setUser($user);
            
            // Définir le statut comme "actif" par défaut
            $offreEmploi->setStatus('actif');  // Remplace "status" par le nom exact de ta propriété
        
            // Définir la date de publication comme la date actuelle
            $offreEmploi->setDatePublication(new \DateTime());  // Remplace "datePublication" par le nom exact de ta propriété
        } else {
            // Gérer le cas où aucun utilisateur n'est connecté
            throw new \Exception('Aucun utilisateur connecté.');
        }
        
        $form = $this->createForm(OffreEmploiType::class, $offreEmploi);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($offreEmploi);
            $entityManager->flush();
            // Affichage du message de succès
            $this->addFlash('success', 'L\'offre d\'emploi a été ajoutée avec succès.');
            $activityLogger->log('Ajout d\'une offre',$user);
        
            return $this->redirectToRoute('app_offreEmploi', [], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('offre_emploi/ajouter.html.twig', [
            'offreEmploi' => $offreEmploi,
            'form' => $form,
        ]);
    }
    //Ajout d'une offre par Admin
    #[Route('/offre/emploi/ajouter/admin', name: 'app_offreEmploi_ajouter_back', methods: ['GET', 'POST'])]
    public function newBack(Request $request, EntityManagerInterface $entityManager, Security $security,ActivityLoggerService $activityLogger): Response
    {
        $offreEmploi = new OffreEmploi();
        
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();
        
        // Vérifier si un utilisateur est connecté
        if ($user) {
            // Associer l'utilisateur connecté à l'offre d'emploi
            $offreEmploi->setUser($user);
            
            // Définir le statut comme "actif" par défaut
            $offreEmploi->setStatus('actif');  // Remplace "status" par le nom exact de ta propriété
        
            // Définir la date de publication comme la date actuelle
            $offreEmploi->setDatePublication(new \DateTime());  // Remplace "datePublication" par le nom exact de ta propriété
        } else {
            // Gérer le cas où aucun utilisateur n'est connecté
            throw new \Exception('Aucun utilisateur connecté.');
        }
        
        $form = $this->createForm(OffreEmploiType::class, $offreEmploi);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($offreEmploi);
            $entityManager->flush();
            // Affichage du message de succès
            $this->addFlash('success', 'L\'offre d\'emploi a été ajoutée avec succès.');
            $activityLogger->log('Ajout d\'une offre',$user);
        
            return $this->redirectToRoute('app_offre_emploi_back', [], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('offre_emploi/ajouterBack.html.twig', [
            'offreEmploi' => $offreEmploi,
            'form' => $form,
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
    // Annulation de candidature par un Employé 
    #[Route('/candidature/annuler/{candidatureId}', name: 'app_annuler_candidature', methods: ['POST'])]
    public function annulerCandidature($candidatureId, EntityManagerInterface $entityManager, CandidatureRepository $candidatureRepository): Response
    {
        // Récupérer la candidature en fonction de l'ID
        $candidature = $candidatureRepository->find($candidatureId);

        // Si la candidature n'existe pas, afficher une erreur
        if (!$candidature) {
            throw $this->createNotFoundException('Candidature non trouvée');
        }

        // Supprimer la candidature
        $entityManager->remove($candidature);
        $entityManager->flush();

        // Ajouter un message de succès
        $this->addFlash('success', 'Votre candidature a été annulée et supprimée.');

        // Rediriger vers la page des candidatures
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
    //Modifier l'état d'une candidature (accepté,refusée) par l'Admin (Supprimé du BackOffice)
    #[Route('/candidature/{id}/modifier-etat', name: 'candidature_modifier_etat', methods: ['POST'])]
    public function modifierEtatCandidature(
        int $id,
        Request $request,
        CandidatureRepository $candidatureRepository,
        OffreEmploiRepository $offreRepository, // Ajoutez l'OffreRepository
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

                    // Si le nombre de postes devient 0, mettre l'état de l'offre à "Inactif"
                    if ($offre->getNombrePostes() === 0) {
                        $offre->setStatus('Inactif'); // Mettre l'état de l'offre à Inactif
                        $entityManager->persist($offre); // Persister l'offre mise à jour
                    }
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

    //Modification de l'état d'une candidature par un agriculteur
    #[Route("/candidature/update/{id}", name: "update_candidature_state", methods: ["POST"])]
    public function updateCandidatureState(
        int $id,
        Request $request,
        CandidatureRepository $candidatureRepository,
        OffreEmploiRepository $offreRepository,
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        SmsService $smsService,
        EmailService $emailService
    ): Response {
        // Récupérer la candidature par son ID
        $candidature = $candidatureRepository->find($id);

        if (!$candidature) {
            throw $this->createNotFoundException("La candidature avec l'ID $id n'existe pas.");
        }

        // Récupérer l'offre associée
        $offre = $candidature->getOffre();

        // Récupérer le nouvel état
        $nouvelEtat = $request->request->get('etat');

        if (!in_array($nouvelEtat, ['Acceptée', 'Refusée'])) {
            $this->addFlash('error', 'État invalide.');
            return $this->redirectToRoute('app_candidatures_offre', ['id' => $offre->getId()]);
        }

        // Mettre à jour l'état
        $candidature->setEtat($nouvelEtat);

        if ($nouvelEtat == 'Acceptée') {
            $offre->setNombrePostes($offre->getNombrePostes() - 1);
            if ($offre->getNombrePostes() <= 0) {
                $offre->setStatus('Inactif');
            }
        }

        //Envoi du SMS
        $phoneNumber = $candidature->getEmploye()->getNumTel();
        if (!str_starts_with($phoneNumber, '+216')) {
            $phoneNumber = '+216' . ltrim($phoneNumber, '0');
        }
        $message = "Votre candidature pour l'offre '{$offre->getTitre()}' a été {$nouvelEtat}.";
        $smsSent = $smsService->sendSms($phoneNumber, $message);
        
        if (!$smsSent) {
            $this->addFlash('warning', 'Le SMS n\'a pas pu être envoyé.');
        }

        //Envoi de l'email 
        $user = $candidature->getEmploye();
        $emailEmploye = $user->getEmail();

        $emailContent = "
            Bonjour,
            <br><br>
            Votre candidature pour l'offre <strong>{$offre->getTitre()}</strong> a été <strong>{$nouvelEtat}</strong>.
            <br><br>
            Cordialement,<br>
            L'équipe WeFarm
        ";

        try {
              $transport = Transport::fromDsn('smtp://benharbfarah85@gmail.com:vevilsdkhkwqczbq@smtp.gmail.com:587?encryption=tls&auth_mode=login');
              $mailer = new Mailer($transport);

            $email = (new Email())
                ->from(new Address('wefarmapplication@gmail.com', 'WeFarm Support'))
                ->to($emailEmploye)
                ->subject('Mise à jour de votre candidature')
                ->html($emailContent);

            $mailer->send($email);
            $this->addFlash('success', 'Email envoyé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l’envoi de l’email: ' . $e->getMessage());
        }

        // Sauvegarde des modifications
        $entityManager->flush();

        // Message de confirmation
        $this->addFlash('success', 'L\'état de la candidature a été mis à jour avec succès.');

        return $this->redirectToRoute('app_candidatures_offre', ['id' => $offre->getId()]);
    }

    //Matching
    #[Route("/offres/{id}/candidatures", name: "app_candidatures_offre", methods: ["GET"])]
    public function candidaturesOffre(
        int $id,
        OffreEmploiRepository $offreEmploiRepository,
        CandidatureRepository $candidatureRepository,
        MatchingService $matchingService
    ): Response {
        // Récupérer l'offre d'emploi par son ID
        $offre = $offreEmploiRepository->find($id);

        // Vérifier si l'offre existe
        if (!$offre) {
            throw $this->createNotFoundException("L'offre avec l'ID $id n'existe pas.");
        }

        // Vérifier si l'utilisateur connecté est l'agriculteur qui a posté l'offre
        if ($offre->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisé à accéder à cette offre.");
        }

        // Récupérer les candidatures associées à cette offre
        $candidatures = $candidatureRepository->findBy(['offre' => $offre]);

        // Compétences requises pour l'offre
        $requiredSkillsStr = $offre->getCompetencesRequises();

        // Tableau associatif pour stocker le pourcentage de correspondance
        $matchPercentages = [];

        // Calculer le pourcentage de correspondance pour chaque candidature
        foreach ($candidatures as $candidature) {
            $candidateSkillsStr = $candidature->getCompetences();
            $matchPercentage = $matchingService->calculateMatchPercentage($requiredSkillsStr, $candidateSkillsStr);
            $matchPercentages[$candidature->getId()] = $matchPercentage;
        }

        return $this->render('offre_emploi/listCandidatures.html.twig', [
            'offre' => $offre,
            'candidatures' => $candidatures,
            'matchPercentages' => $matchPercentages, 
        ]);
    }
    //Statistiques des offre par Admin
    #[Route('/offre-emploi/statistiques', name: 'emploi_statistiques')]
    public function statistiques()
    {
        // Récupérer toutes les offres d'emploi
        $offres = $this->offreEmploiRepository->findAll();

        // Initialiser un tableau pour stocker les totaux par saison
        $totauxParSaison = [
            'spring' => 0,
            'summer' => 0,
            'autumn' => 0,
            'winter' => 0,
        ];

        // Calculer le pourcentage de chaque saison pour chaque offre
        foreach ($offres as $offre) {
            $progressionParSaison = $this->offreEmploiRepository->calculateSeasonsProgress($offre);

            // Ajouter les pourcentages au total
            foreach ($progressionParSaison as $saison => $pourcentage) {
                $totauxParSaison[$saison] += $pourcentage;
            }
        }

        // Calculer la moyenne par saison
        $nombreOffres = count($offres);
        if ($nombreOffres > 0) {
            foreach ($totauxParSaison as $saison => $total) {
                $totauxParSaison[$saison] = $total / $nombreOffres;
            }
        }

        $percentageByGovernorat = $this->offreEmploiRepository->calculatePercentageByGovernorat();

        return $this->render('offre_emploi/statistiques.html.twig', [
            'totauxParSaison' => $totauxParSaison,
            'percentageByGovernorat' => $percentageByGovernorat,
        ]);
    }
    //Recherche par titre
    #[Route('/offres/recherche', name: 'offres_recherche', methods: ['GET'])]
    public function rechercherOffresParTitre(Request $request, OffreEmploiRepository $offreEmploiRepository): Response
    {
        $searchTerm = $request->query->get('search', '');
        $offres = $offreEmploiRepository->searchByTitle($searchTerm);

        return $this->render('offre_emploi/index.html.twig', [
            'offres' => $offres,
            'searchTerm' => $searchTerm
        ]);
    }
    //Recherche par compétences
    #[Route('/offres/recherche/employe', name: 'offres_recherche_employe', methods: ['GET'])]
    public function rechercherOffresParCompetences(
        Request $request,
        OffreEmploiRepository $offreEmploiRepository,
        RequestStack $requestStack // Ajoutez RequestStack ici
    ): Response {
        $searchTerm = $request->query->get('search', '');
        $offres = $offreEmploiRepository->searchByCompetence($searchTerm);

        // Gestion des formulaires pour éviter l'erreur de formulaireCandidature n'existe pas lors de la recherche
        $formulaireOffres = [];
        foreach ($offres as $offre) {
            $candidature = new Candidature();
            $form = $this->createForm(CandidatureType::class, $candidature);
            $formulaireOffres[$offre->getId()] = $form->createView();
        }

        // Récupérer la locale active (par défaut 'fr')
        $locale = $requestStack->getCurrentRequest()->getLocale();  // Récupère la langue active
        $traduction_active = $locale;  // Assigner la locale active à la variable

        return $this->render('offre_emploi/indexEmploye.html.twig', [
            'offres' => $offres,
            'searchTerm' => $searchTerm,
            'formulaireOffres' => $formulaireOffres,
            'modal_open' => false,
            'traduction_active' => $traduction_active, // Passer la variable à la vue
        ]);
    }
    //Traduction
    #[Route('/offres', name: 'liste_offres')]
    public function afficherOffres(EntityManagerInterface $entityManager, Request $request): Response
    {
        // Initialisation de la traduction avec GoogleTranslate
        $translator = new GoogleTranslate();
        $translator->setTarget('ar'); // Traduction en arabe par défaut

        // Récupération des offres depuis la base de données
        $offres = $entityManager->getRepository(OffreEmploi::class)->findAll();
        
        // Vérifie si le paramètre 'traduire' existe dans l'URL
        $traduire = $request->query->get('traduire'); // 'ar' pour arabe, sinon pas de traduction

        if ($traduire === 'ar') {
            // Si 'traduire' est défini à 'ar', traduire les champs des offres en arabe
            foreach ($offres as $offre) {
                $offre->setTitre($translator->translate($offre->getTitre()));
                $offre->setCompetencesRequises($translator->translate($offre->getCompetencesRequises()));
                $offre->setDescription($translator->translate($offre->getDescription()));
                $offre->setLocalisation($translator->translate($offre->getLocalisation()));
            }
        } else {
            // Si 'traduire' n'est pas 'ar', ne pas effectuer de traduction, garder le contenu original (français)
            foreach ($offres as $offre) {
                // Ici, tu peux ajouter un code pour gérer d'autres traductions si nécessaire
                // Exemple : $offre->setTitre($offre->getTitre()); pour garder le titre en français
            }
        }

        // Création des formulaires de candidature pour chaque offre
        $formulaireOffres = [];
        foreach ($offres as $offre) {
            $candidature = new Candidature();
            $form = $this->createForm(CandidatureType::class, $candidature);
            $formulaireOffres[$offre->getId()] = $form->createView();
        }

        // Retourne la vue avec les données nécessaires
        return $this->render('offre_emploi/indexEmploye.html.twig', [
            'offres' => $offres,
            'traduction_active' => $traduire, // Indique si la traduction est activée
            'formulaireOffres' => $formulaireOffres,
            'modal_open' => false
        ]);
    }
    //Calendrier
    #[Route('/calendrier', name: 'app_calendrier')]
    public function calendrier(Security $security, EntityManagerInterface $em): Response
    {
        // Récupérer l'année en cours
        $currentYear = new \DateTime();
        $year = $currentYear->format('Y');  // L'année actuelle (ex: 2025)

        // Récupérer l'utilisateur connecté
        $user = $security->getUser(); // L'utilisateur connecté

        // Rechercher toutes les candidatures acceptées de l'utilisateur connecté
        $candidatures = $em->getRepository(Candidature::class)
            ->createQueryBuilder('c')
            ->join('c.offre', 'o')  // Lier la table Offre à la table Candidature
            ->where('c.employe = :user')  // L'utilisateur connecté (employé)
            ->andWhere('c.etat = :accepted')  // L'état de la candidature est "accepté"
            ->setParameter('user', $user)
            ->setParameter('accepted', 'Acceptée')
            ->getQuery()
            ->getResult();

        // Créer un tableau de mois de l'année en cours
        $months = [];
        for ($month = 1; $month <= 12; $month++) {
            $firstDayOfMonth = new \DateTime("$year-$month-01");
            $lastDayOfMonth = new \DateTime($firstDayOfMonth->format('Y-m-t'));

            // Calculer le premier jour de la semaine du mois
            $firstDayOfWeek = $firstDayOfMonth->format('w'); // 0 = Dimanche, 6 = Samedi

            // Nombre de jours dans le mois
            $daysInMonth = (int) $lastDayOfMonth->format('d');

            // Créer un tableau pour les jours du mois
            $days = [];
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $days[] = new \DateTime("$year-$month-$day");
            }

            // Créer un tableau d'événements à afficher dans le calendrier
            $events = [];
            foreach ($candidatures as $candidature) {
                $offerStartDate = $candidature->getOffre()->getDateDebut(); // Date de début de l'offre (assurez-vous que cette date existe)
                $offerTitle = $candidature->getOffre()->getTitre(); // Titre de l'offre

                // Ajouter l'événement à la date correspondante (date de début de l'offre)
                if ($offerStartDate->format('Y-m') == $firstDayOfMonth->format('Y-m')) {
                    $events[] = [
                        'date' => $offerStartDate->format('Y-m-d'),
                        'title' => $offerTitle, // Titre de l'offre
                        'offreId' => $candidature->getOffre()->getId()
                    ];
                }
            }

            // Ajouter le mois et ses jours au tableau des mois
            $months[] = [
                'month' => $firstDayOfMonth->format('F'), // Nom complet du mois (Janvier, Février, etc.)
                'daysInMonth' => $days,
                'firstDayOfWeek' => $firstDayOfWeek,
                'events' => $events // Ajouter les événements à chaque mois
            ];
        }

        // Passer les données des mois et les événements à la vue
        return $this->render('offre_emploi/calendrier.html.twig', [
            'months' => $months,
        ]);
    }

    

}
