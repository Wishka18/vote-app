<?php

namespace App\Repository;

use App\Entity\Vote;
use App\Enum\Position;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vote>
 */
class VoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    //    /**
    //     * @return Vote[] Returns an array of Vote objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Vote
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findOneByMemberAndPosition($member, \App\Enum\Position $position): ?Vote
    {
        return $this->createQueryBuilder('v')
            ->join('v.candidate', 'c')
            ->where('v.member = :member')
            ->andWhere('c.position = :position')
            ->setParameter('member', $member)
            ->setParameter('position', $position)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countAllVotes(): int
    {
        return $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getLiveLeaderForPosition(Position $position): ?array
    {
        $qb = $this->createQueryBuilder('v')
            ->select('c.name, COUNT(v.id) as voteCount')
            ->join('v.candidate', 'c')
            ->where('c.position = :position')
            ->setParameter('position', $position)
            ->groupBy('c.id')
            ->orderBy('voteCount', 'DESC')
            ->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();

        if ($result) {
            $totalVotesForPosition = $this->createQueryBuilder('v')
                ->select('COUNT(v.id)')
                ->join('v.candidate', 'c')
                ->where('c.position = :position')
                ->setParameter('position', $position)
                ->getQuery()
                ->getSingleScalarResult();

            $percentage = $totalVotesForPosition > 0
                ? ($result['voteCount'] / $totalVotesForPosition) * 100
                : 0;

            return [
                'name' => $result['name'],
                'votes' => $result['voteCount'],
                'percentage' => $percentage
            ];
        }

        return null;
    }
}
