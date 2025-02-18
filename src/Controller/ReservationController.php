<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Reservation;
use Symfony\Component\HttpFoundation\Request;
use App\Controller\ReservationController;
use App\Entity\Evenement;
use App\Repository\EvenementRepository;





class ReservationController extends AbstractController
{
    private EntityManagerInterface $entityManager;  // Déclarer la propriété entityManager

    // Injection de EntityManagerInterface via le constructeur
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/reservation', name: 'app_reservation')]
    public function index(): Response
    {
        return $this->render('reservation/index.html.twig', [
            'controller_name' => 'ReservationController',
        ]);
    }
    // Route pour afficher les réservations de l'utilisateur connecté
    #[Route('/mes-reservationss', name: 'app_mes_reservations')]
    public function mesReservations(Security $security, ReservationRepository $reservationRepository): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer les réservations de l'utilisateur connecté avec les événements associés (Eager loading)
        $reservations = $reservationRepository->createQueryBuilder('r')
            ->leftJoin('r.evenement', 'e') // Jointure avec l'entité Evenement
            ->addSelect('e') // Forcer l'inclusion de l'événement dans le résultat
            ->where('r.client = :client') // Filtrer par l'utilisateur connecté (client)
            ->setParameter('client', $user) // Passer l'utilisateur comme paramètre
            ->getQuery()
            ->getResult(); // Exécuter la requête et récupérer les résultats

        // Renvoyer la réponse avec les réservations et les événements associés
        return $this->render('events/client_reservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }
    // Route pour annuler une réservation
    #[Route('/annuler-reservation/{id}', name: 'app_reservation_cancel')]
    public function cancelReservation($id, ReservationRepository $reservationRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérer la réservation par son ID
        $reservation = $reservationRepository->find($id);

        // Si la réservation n'existe pas
        if (!$reservation) {
            $this->addFlash('error', 'La réservation n\'existe pas.');
            return $this->redirectToRoute('app_mes_reservations');
        }

        // Supprimer la réservation
        $entityManager->remove($reservation);
        $entityManager->flush();

        // Afficher un message de succès
        $this->addFlash('success', 'Réservation annulée avec succès.');

        // Rediriger vers la liste des réservations
        return $this->redirectToRoute('app_mes_reservations');
    }
    #[Route('/reservations', name: 'app_user_reservations', methods: ['GET'])]
public function showReservations(Security $security, ReservationRepository $reservationRepository, EvenementRepository $evenementRepository): Response
{
    // Récupérer l'utilisateur connecté
    $user = $security->getUser();

   

    // Récupérer les événements créés par l'agriculteur connecté
    $evenements = $evenementRepository->findBy(['agriculteur' => $user]);

    // Extraire les IDs des événements
    $evenementIds = array_map(fn($evenement) => $evenement->getId(), $evenements);

    // Récupérer les réservations associées aux événements de cet agriculteur
    $reservations = $reservationRepository->createQueryBuilder('r')
        ->join('r.evenement', 'e')
        ->where('e.id IN (:evenementIds)')
        ->setParameter('evenementIds', $evenementIds)
        ->getQuery()
        ->getResult();

    return $this->render('events/reservations.html.twig', [
        'reservations' => $reservations,
    ]);
}
    // Route pour modifier l'état de la réservation
    #[Route('/reservations/{id}/update-status', name: 'app_reservation_update_status', methods: ['POST'])]
    public function updateStatus(Reservation $reservation, Request $request): Response
    {
        
        // Récupérer l'état depuis la requête
        $etat = $request->request->get('etat');
        $reservation->setEtat($etat); // Mise à jour de l'état

        // Sauvegarde en base de données avec l'EntityManager injecté
        $this->entityManager->flush();

        // Message de confirmation
        $this->addFlash('success', 'L\'état de la réservation a été modifié avec succès.');

        return $this->redirectToRoute('app_user_reservations');
    }
    #[Route('/evenement/{id}/reservations', name: 'app_reservations_by_event', methods: ['GET'])]
    public function reservationsByEvent(Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        // Récupérer les réservations associées à l'événement
        $reservations = $entityManager->getRepository(Reservation::class)->findBy(['evenement' => $evenement]);

        return $this->render('events/show_reservations.html.twig', [
            'evenement' => $evenement,
            'reservations' => $reservations,
        ]);
    }
    #[Route('/reservation/{id}/delete', name: 'app_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservations_by_event', ['id' => $reservation->getEvenement()->getId()]);
    }

    
}
