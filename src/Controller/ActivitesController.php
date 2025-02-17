<?php
namespace App\Controller;

use App\Entity\Activites;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Utilisateur;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ActivitesRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Form\ActivityType;
class ActivitesController extends AbstractController
{
    #[Route('/admin/activites', name: 'app_activites')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer toutes les activités
        $activities = $entityManager->getRepository(Activites::class)->findBy([], ['date' => 'DESC']);

        return $this->render('activites/index.html.twig', [
            'activities' => $activities,
        ]);
    }
    #[Route('/admin/activities', name: 'admin_activities')]
    public function showActivities(Request $request, ActivitesRepository $activitesRepository): Response
{
    $search = $request->query->get('search'); // Récupérer la valeur du champ de recherche

    $queryBuilder = $activitesRepository->createQueryBuilder('a')
        ->leftJoin('a.utilisateur', 'u') // Joindre la table User pour chercher par email
        ->orderBy('a.date', 'DESC');

    if ($search) {
        $queryBuilder
            ->andWhere('u.email LIKE :search OR a.action LIKE :search OR a.date LIKE :search')
            ->setParameter('search', '%' . $search . '%');
    }

    $activities = $queryBuilder->getQuery()->getResult();

    return $this->render('admin/user_activities.html.twig', [
        'activities' => $activities,
        'search' => $search
    ]);
}
    /*#[Route('/admin/user/{id}/activities', name: 'admin_user_activities')]
public function userActivities(int $id, EntityManagerInterface $entityManager): Response
{
    $user = $entityManager->getRepository(Utilisateur::class)->find($id);
    if (!$user) {
        throw $this->createNotFoundException("Utilisateur non trouvé");
    }

    $activities = $entityManager->getRepository(Activites::class)->findBy(
        ['utilisateur' => $user],
        ['date' => 'DESC']
    );

    return $this->render('admin/user_activities.html.twig', [
        'user' => $user,
        'activities' => $activities
    ]);
}*/
public function addActivity(Request $request, EntityManagerInterface $entityManager)
{
    $activity = new Activites();
    $activity->setDate(new \DateTime());

    // Récupérer l'adresse IP
    $ip = $request->getClientIp();
    $activity->addMetadata('ip_address', $ip);

    // Récupérer l'utilisateur connecté
    $user = $this->getUser();
    $activity->setUser($user);

    // Sauvegarde en base
    $entityManager->persist($activity);
    $entityManager->flush();

    return $this->redirectToRoute('some_route');
}

#[Route('/admin/activity/delete/{id}', name: 'admin_activity_delete', methods: ['POST', 'GET'])]
public function deleteActivity($id, EntityManagerInterface $entityManager, ActivitesRepository $activitesRepository): Response
{
    $activity = $activitesRepository->find($id);
    
    if (!$activity) {
        throw $this->createNotFoundException('Activité non trouvée');
    }

    $entityManager->remove($activity);
    $entityManager->flush();

    return $this->redirectToRoute('admin_activities');
}
#[Route('/admin/activities/stats', name: 'admin_activities_stats')]
public function activitiesStats(ActivitesRepository $activitesRepository): Response
{
    // Récupérer toutes les activités
    $activities = $activitesRepository->findAll();

    // Organiser les données pour les statistiques
    $stats = [
        'monthly' => [],
        'weekly' => [],
        'yearly' => [],
        'categories' => []
    ];

    foreach ($activities as $activity) {
        $date = $activity->getDate();
        $month = $date->format('Y-m'); // Ex: 2024-02
        $week = $date->format('o-W'); // Ex: 2024-06 (année et numéro de semaine)
        $year = $date->format('Y'); // Ex: 2024
        $category = $activity->getAction(); // Catégorie d'activité

        // Compter les activités par mois, semaine, année
        $stats['monthly'][$month] = ($stats['monthly'][$month] ?? 0) + 1;
        $stats['weekly'][$week] = ($stats['weekly'][$week] ?? 0) + 1;
        $stats['yearly'][$year] = ($stats['yearly'][$year] ?? 0) + 1;

        // Regrouper par catégorie
        $stats['categories'][$category] = ($stats['categories'][$category] ?? 0) + 1;
    }

    return $this->render('admin/activities_stats.html.twig', [
        'stats' => $stats
    ]);
}
#[Route('/admin/activities/stats/data', name: 'admin_activities_stats_data')]
public function getStatsData(ActivitesRepository $activitesRepository): JsonResponse
{
    // Même logique que la méthode précédente, mais renvoyée en JSON
    $activities = $activitesRepository->findAll();

    $weeklyStats = [];
    $monthlyStats = [];
    $yearlyStats = [];
    $categoryStats = [];

    foreach ($activities as $activity) {
        $category = $activity->getAction();
        $date = $activity->getDate();
        $week = $date->format('W');
        $month = $date->format('M');
        $year = $date->format('Y');

        $weeklyStats[$week] = ($weeklyStats[$week] ?? 0) + 1;
        $monthlyStats[$month] = ($monthlyStats[$month] ?? 0) + 1;
        $yearlyStats[$year] = ($yearlyStats[$year] ?? 0) + 1;
          // Comptage des activités par catégorie
          if (!isset($categoryStats[$category])) {
            $categoryStats[$category] = ['weekly' => [], 'monthly' => [], 'yearly' => []];
        }

        $categoryStats[$category]['weekly'][$week] = ($categoryStats[$category]['weekly'][$week] ?? 0) + 1;
        $categoryStats[$category]['monthly'][$month] = ($categoryStats[$category]['monthly'][$month] ?? 0) + 1;
        $categoryStats[$category]['yearly'][$year] = ($categoryStats[$category]['yearly'][$year] ?? 0) + 1;
    }
    

    return new JsonResponse([
        'weekly' => $weeklyStats,
        'monthly' => $monthlyStats,
        'yearly' => $yearlyStats,
        'categories' => $categoryStats
    ]);
}

#[Route('/admin/activity/edit/{id}', name: 'admin_activity_edit')]
public function editActivity(Request $request, Activites $activity, EntityManagerInterface $entityManager): Response
{
    // Création du formulaire
    $form = $this->createForm(ActivityType::class, $activity);
    $form->handleRequest($request);

    // Vérification et enregistrement des modifications
    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        $this->addFlash('success', 'L\'activité a été modifiée avec succès !');
        return $this->redirectToRoute('admin_activities');
    }

    return $this->render('admin/activity_edit.html.twig', [
        'form' => $form->createView(),
        'activity' => $activity
    ]);
}


}
