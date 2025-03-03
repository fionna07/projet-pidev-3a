<?php

namespace App\Repository;

use App\Entity\OffreEmploi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OffreEmploi>
 */
class OffreEmploiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OffreEmploi::class);
    }

    //    /**
    //     * @return OffreEmploi[] Returns an array of OffreEmploi objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?OffreEmploi
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findAll(): array
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.datePublication', 'DESC') // Trier par date de publication (optionnel)
            ->getQuery()
            ->getResult();
    }

    //Calcule le pourcentage de progression par saison pour une offre d'emploi
    public function calculateSeasonsProgress(OffreEmploi $offreEmploi): array
    {
        $startDate = $offreEmploi->getDateDebut();
        $endDate = $offreEmploi->getDateFinEstimee();

        // Calcul du nombre de jours total entre la date de début et de fin
        $totalDays = $startDate->diff($endDate)->days;

        // Initialisation du tableau pour le pourcentage de chaque saison
        $seasonsProgress = [
            'spring' => 0,
            'summer' => 0,
            'autumn' => 0,
            'winter' => 0,
        ];

        // Calcul du pourcentage pour chaque saison
        foreach (['spring', 'summer', 'autumn', 'winter'] as $season) {
            $seasonStartDate = $this->getSeasonStartDate($season, $startDate->format('Y'));
            $seasonEndDate = $this->getSeasonEndDate($season, $startDate->format('Y'));

            // Si l'offre d'emploi se trouve dans la saison
            if ($startDate <= $seasonEndDate && $endDate >= $seasonStartDate) {
                $overlapStart = max($startDate, $seasonStartDate);
                $overlapEnd = min($endDate, $seasonEndDate);

                // Calcul des jours de chevauchement pour cette saison
                $overlapDays = $overlapStart->diff($overlapEnd)->days;
                $seasonsProgress[$season] = ($overlapDays / $totalDays) * 100;
            }
        }

        return $seasonsProgress;
    }

    private function getSeasonStartDate(string $season, string $year): \DateTimeInterface
    {
        switch ($season) {
            case 'spring':
                return new \DateTime("$year-03-21");
            case 'summer':
                return new \DateTime("$year-06-21");
            case 'autumn':
                return new \DateTime("$year-09-23");
            case 'winter':
                return new \DateTime("$year-12-21");
            default:
                throw new \Exception("Saison inconnue");
        }
    }

    private function getSeasonEndDate(string $season, string $year): \DateTimeInterface
    {
        switch ($season) {
            case 'spring':
                return new \DateTime("$year-06-20");
            case 'summer':
                return new \DateTime("$year-09-22");
            case 'autumn':
                return new \DateTime("$year-12-20");
            case 'winter':
                return new \DateTime(($year + 1) . "-03-20");
            default:
                throw new \Exception("Saison inconnue");
        }
    }
    public function calculatePercentageByGovernorat(): array
    {
        // Récupérer toutes les offres d'emploi
        $offres = $this->findAll();

        // Initialiser un tableau pour compter les offres par gouvernorat
        $countByGovernorat = [];
        $totalOffres = count($offres);

        // Compter les offres par gouvernorat
        foreach ($offres as $offre) {
            $gouvernorat = $offre->getLocalisation(); 
            if (!isset($countByGovernorat[$gouvernorat])) {
                $countByGovernorat[$gouvernorat] = 0;
            }
            $countByGovernorat[$gouvernorat]++;
        }

        // Calculer le pourcentage pour chaque gouvernorat
        $percentageByGovernorat = [];
        foreach ($countByGovernorat as $gouvernorat => $count) {
            $percentageByGovernorat[$gouvernorat] = ($count / $totalOffres) * 100;
        }

        return $percentageByGovernorat;
    }
    public function searchByTitle(string $searchTerm): array
    {
        return $this->createQueryBuilder('o')
            ->where('LOWER(o.titre) LIKE LOWER(:searchTerm)')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->getQuery()
            ->getResult();
    }
    public function searchByCompetence(string $searchTerm): array
    {
        return $this->createQueryBuilder('o')
            ->where('LOWER(o.competencesRequises) LIKE LOWER(:searchTerm)')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->getQuery()
            ->getResult();
    }

}
