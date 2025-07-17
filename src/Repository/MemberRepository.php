<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Member>
 */
class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }


    public function countActiveMembers(): int
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.duesPaid = true')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findWithPayments(int $year, string $status = 'all', string $search = ''): array
    {
        $query = $this->createQueryBuilder('m')
            ->orderBy('m.name', 'ASC');

        // Apply status filter if needed
        if ($status !== 'all') {
            $query->andWhere('m.duesPaid = :status')
                ->setParameter('status', $status === 'paid');
        }

        // Apply search filter if needed
        if (!empty($search)) {
            $query->andWhere('m.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return $query->getQuery()->getResult();
    }

    public function saveAll(): void
    {
        $this->_em->flush();
    }
}
