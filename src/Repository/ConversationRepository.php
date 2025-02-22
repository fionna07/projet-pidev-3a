<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

     // Méthode pour récupérer toutes les conversations d'un participant
     public function findByParticipant(Utilisateur $participant)
     {
         return $this->createQueryBuilder('c')
             ->join('c.participants', 'p')
             ->where('p = :participant')
             ->setParameter('participant', $participant)
             ->getQuery()
             ->getResult();
     }

    public function findExistingConversation(Utilisateur $user1, Utilisateur $user2): ?Conversation
{
    return $this->createQueryBuilder('c')
        ->join('c.participants', 'p')
        ->where('p = :user1 OR p = :user2')
        ->setParameter('user1', $user1)
        ->setParameter('user2', $user2)
        ->groupBy('c.id')
        ->having('COUNT(DISTINCT p) = 2')
        ->getQuery()
        ->getOneOrNullResult();
}

}