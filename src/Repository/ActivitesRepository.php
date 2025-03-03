<?php

namespace App\Repository;

use App\Entity\Activites;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Activites>
 */
class ActivitesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activites::class);
    }
    public function getStatsByActivityType()
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            "SELECT a.type, 
                    COUNT(a.id) as total,
                    SUM(CASE WHEN a.date >= :weekStart THEN 1 ELSE 0 END) as weekly,
                    SUM(CASE WHEN a.date >= :monthStart THEN 1 ELSE 0 END) as monthly
             FROM App\Entity\Activity a
             WHERE a.date >= :monthStart
             GROUP BY a.type"
        );

        $query->setParameter('weekStart', new \DateTime('-7 days'));
        $query->setParameter('monthStart', new \DateTime('-30 days'));

        return $query->getResult();
    }

//    /**
//     * @return Activites[] Returns an array of Activites objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Activites
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
