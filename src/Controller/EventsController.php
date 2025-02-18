<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security; 
use App\Entity\Reservation;
use App\Repository\ReservationRepository;


#[Route('/events')]
final class EventsController extends AbstractController
{
    #[Route(name: 'app_events_index', methods: ['GET'])]
    public function index(EvenementRepository $evenementRepository): Response
    {
        return $this->render('events/index.html.twig', [
            'evenements' => $evenementRepository->findAll(),
        ]);
    }
    #[Route(name: 'app_events_index_back', methods: ['GET'])]
    public function indexBack(EvenementRepository $evenementRepository): Response
    {
        return $this->render('events/indexBack.html.twig', [
            'evenements' => $evenementRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_events_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $evenement = new Evenement();

        // Récupérer l'utilisateur connecté
        $user = $security->getUser();

        // Vérifier si un utilisateur est connecté
        if ($user) {
            // Associer l'utilisateur connecté à l'événement
            $evenement->setAgriculteur($user);
        } else {
            // Gérer le cas où aucun utilisateur n'est connecté (optionnel)
            throw new \Exception('Aucun utilisateur connecté.');
        }

        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($evenement);
            $entityManager->flush();

            return $this->redirectToRoute('app_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('events/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }
    //Ajout d'un événement Back
    #[Route('/new/admin', name: 'app_events_new_back', methods: ['GET', 'POST'])]
    public function newBack(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $evenement = new Evenement();

        // Récupérer l'utilisateur connecté
        $user = $security->getUser();

        // Vérifier si un utilisateur est connecté
        if ($user) {
            // Associer l'utilisateur connecté à l'événement
            $evenement->setAgriculteur($user);
        } else {
            // Gérer le cas où aucun utilisateur n'est connecté (optionnel)
            throw new \Exception('Aucun utilisateur connecté.');
        }

        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($evenement);
            $entityManager->flush();

            return $this->redirectToRoute('back_events', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('events/newBack.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }
    //Affichage des événement pour Agriculteur
    #[Route('/{id}', name: 'app_events_show', methods: ['GET'])]
    public function show(Evenement $evenement): Response
    {
        return $this->render('events/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }
    //Affichage des événement pour Client
    #[Route('/{id}/client', name: 'app_events_show_client', methods: ['GET'])]
    public function showClient(Evenement $evenement): Response
    {
        return $this->render('events/showClient.html.twig', [
            'evenement' => $evenement,
        ]);
    }
    //Affichage des événement pour Admin
    #[Route('/{id}/admin', name: 'app_events_show_back', methods: ['GET'])]
    public function showBack(Evenement $evenement): Response
    {
        return $this->render('events/showBack.html.twig', [
            'evenement' => $evenement,
        ]);
    }  
    #[Route('/{id}/edit', name: 'app_events_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('events/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }
    //Supprimer Event par Agriculteur
    #[Route('/{id}', name: 'app_events_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        // Vérification du token CSRF pour la sécurité
        if ($this->isCsrfTokenValid('delete' . $evenement->getId(), $request->get('_token'))) {
            
            // Dissocier l'agriculteur de l'événement (mettre à NULL l'agriculteur)
            $evenement->setAgriculteur(null); // Assurez-vous que setAgriculteur() est une méthode valide dans votre entité

            // Supprimer l'événement
            $entityManager->remove($evenement);
            $entityManager->flush(); // Appliquer les modifications en base de données
        }

        // Rediriger vers la page d'index des événements
        return $this->redirectToRoute('app_events_index', [], Response::HTTP_SEE_OTHER);
    }
    //Supprimer event par Admin
    #[Route('/{id}/admin', name: 'app_events_delete_back', methods: ['POST'])]
    public function deleteBack(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        // Vérification du token CSRF pour la sécurité
        if ($this->isCsrfTokenValid('delete' . $evenement->getId(), $request->get('_token'))) {
            
            // Dissocier l'agriculteur de l'événement (mettre à NULL l'agriculteur)
            $evenement->setAgriculteur(null); // Assurez-vous que setAgriculteur() est une méthode valide dans votre entité

            // Supprimer l'événement
            $entityManager->remove($evenement);
            $entityManager->flush(); // Appliquer les modifications en base de données
        }

        // Rediriger vers la page d'index des événements
        return $this->redirectToRoute('back_events', [], Response::HTTP_SEE_OTHER);
    }

    //BackOffice
    #[Route('/{id}/edit/back', name: 'app_events_edit_back', methods: ['GET', 'POST'])]
    public function editBack(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('back_events', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('events/editBack.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }
    //Ajouter Réservation
    #[Route('/new/{id}', name: 'app_reservation_new', methods: ['GET'])]
    public function newReservation(int $id, EntityManagerInterface $entityManager, Security $security): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer l'événement
        $evenement = $entityManager->getRepository(Evenement::class)->find($id);
        if (!$evenement) {
            throw $this->createNotFoundException('Événement non trouvé.');
        }

        // Vérifier si une réservation existe déjà pour ce client et cet événement
        $existingReservation = $entityManager->getRepository(Reservation::class)->findOneBy([
            'client' => $user,
            'evenement' => $evenement
        ]);

        if ($existingReservation) {
            $this->addFlash('error', 'Vous avez déjà réservé cet événement.');
            return $this->redirectToRoute('app_events_client');
        }

        // Créer la nouvelle réservation
        $reservation = new Reservation();
        $reservation->setDateReservation(new \DateTime());
        $reservation->setEtat('en attente');
        $reservation->setEvenement($evenement);
        $reservation->setClient($user);

        $entityManager->persist($reservation);
        $entityManager->flush();

        $this->addFlash('success', 'Réservation effectuée avec succès !');
        return $this->redirectToRoute('app_events_client');
    }
    //Voir les réservation pour un Client connecté
    #[Route('/mes-reservations', name: 'app_reservation_client')]
    public function mesReservations(Security $security, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();

        

        // Récupérer les réservations de l'utilisateur
        $reservations = $ReservationRepository->findBy(['client' => $user]);

        return $this->render('events/client_reservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }
    
    
}
