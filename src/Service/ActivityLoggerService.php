<?php
namespace App\Service;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Utilisateur;
use App\Entity\Activites;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ActivityLoggerService
{
    private EntityManagerInterface $entityManager;
    private RequestStack $requestStack;
    private Security $security; // Déclare la propriété

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack,Security $security)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->security = $security;
    }

    public function log(string $action, ?Utilisateur $utilisateur)
    {
       
         // Si l'utilisateur n'est pas fourni, on récupère l'utilisateur connecté
    if ($utilisateur === null) {
        $utilisateur = $this->security->getUser();
    }

    // Vérifier que l'utilisateur est valide avant de continuer
    if (!$utilisateur instanceof Utilisateur) {
        return; // On ne génère pas d'exception, on sort simplement de la fonction
    }

        $activity = new Activites();
        $activity->setUser($utilisateur);
        $activity->setAction($action);
        $activity->setMetadata([
        $utilisateur->getEmail(),
          
           
        ]);
        $activity->setDate(new \DateTime());

        $this->entityManager->persist($activity);
        $this->entityManager->flush();
    }
}
