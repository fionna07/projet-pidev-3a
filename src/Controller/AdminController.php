<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
final class AdminController extends AbstractController{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('back_pages/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

#[Route(path: '/login', name: 'app_login')]
public function login(AuthenticationUtils $authenticationUtils): Response
{
$user = $this->getUser(); 

// Vérifier si l'utilisateur est déjà connecté
if ($user instanceof Utilisateur) {
    // Vérifier le rôle de l'utilisateur
    if (in_array('ROLE_USER', $user->getRoles())) {
        return $this->redirectToRoute('admin_dashboard');
    } else {
        return $this->redirectToRoute('app_front');
    }
}

// Récupérer l'erreur de connexion s'il y en a une
$error = $authenticationUtils->getLastAuthenticationError();
// Récupérer le dernier nom d'utilisateur saisi
$lastUsername = $authenticationUtils->getLastUsername();

return $this->render('security/login.html.twig', [
    'last_username' => $lastUsername, 
    'error' => $error
]);
}
    #[Route('/admin/users', name: 'admin_users')]
public function listUsers(EntityManagerInterface $entityManager): Response
{
    $users = $entityManager->getRepository(Utilisateur::class)->findAll();

    return $this->render('admin/users.html.twig', [
        'users' => $users,
    ]);
}
#[Route('/admin/user/add', name: 'user_add')]
    public function addUser(Request $request): Response
    {
        // Logique d'ajout d'utilisateur (formulaire, etc.)
        return $this->render('admin/add_user.html.twig');
    }
    #[Route('/admin/user/{id}', name: 'user_show', requirements: ['id' => '\d+'])]
    public function show(User $user): Response
    {
        return $this->render('admin/user_show.html.twig', ['user' => $user]);
    }

    #[Route('/admin/user/{id}/edit', name: 'user_edit', requirements: ['id' => '\d+'])]
    public function edit(User $user, Request $request, EntityManagerInterface $em): Response
    {
        // Logique d'édition ici
        return $this->render('admin/user_edit.html.twig', ['user' => $user]);
    }

    #[Route('/admin/user/{id}/delete', name: 'user_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(User $user, EntityManagerInterface $em): Response
    {
        $em->remove($user);
        $em->flush();
        return $this->redirectToRoute('user_list'); // Assurez-vous que 'user_list' existe
    }

    #[Route('/admin/user/{id}/disable', name: 'user_disable', requirements: ['id' => '\d+'])]
    public function disable(User $user, EntityManagerInterface $em): Response
    {
        $user->setStatus('désactivé');
        $em->flush();
        return $this->redirectToRoute('user_list');
    }
    #[Route('/admin/user/{id}/enable', name: 'user_enable', requirements: ['id' => '\d+'])]
public function enable(User $user, EntityManagerInterface $em): Response
{
    $user->setStatus('actif');
    $em->flush();
    return $this->redirectToRoute('user_list'); // Assurez-vous que 'user_list' existe
}

}
