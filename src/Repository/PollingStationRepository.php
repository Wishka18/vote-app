<?php

namespace App\Repository;

use App\Entity\Candidate;
use App\Entity\PollingStation;
use App\Entity\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PollingStation>
 */
class PollingStationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PollingStation::class);
    }

    //    /**
    //     * @return PollingStation[] Returns an array of PollingStation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?PollingStation
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }



    public function countAllStations(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countOpenStations(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.isOpen = true')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Fetch all stations with candidate counts and vote counts
     */
    public function getStationsWithStats(): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('
            ps.id AS stationId,
            ps,
            COUNT(DISTINCT c.id) AS candidateCount,
            COUNT(DISTINCT v.member) AS voteCount
        ')
            ->from(PollingStation::class, 'ps')
            ->leftJoin('App\Entity\Candidate', 'c', 'WITH', 'c.position = ps.position') // Compare enum values directly
            ->leftJoin('App\Entity\Vote', 'v', 'WITH', 'v.candidate = c')
            ->groupBy('ps.id');

        $results = $qb->getQuery()->getResult();

        // Transform to an array indexed by station ID
        $stats = [];
        foreach ($results as $row) {
            $stats[$row['stationId']] = [
                'station' => $row[0], // ✅ Entity is at numeric index 0
                'candidates' => $row['candidateCount'],
                'votes' => $row['voteCount'],
            ];
        }

        return $stats;
    }
}
