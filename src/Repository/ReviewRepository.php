<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function findAllSorted(string $sort = 'createdAt', string $order = 'DESC'): array
    {
        $allowedSorts = ['createdAt', 'rating', 'numberTone'];
        $allowedOrder = ['ASC', 'DESC'];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'createdAt';
        }

        if (!in_array(strtoupper($order), $allowedOrder, true)) {
            $order = 'DESC';
        }

        return $this->createQueryBuilder('r')
            ->orderBy("r.$sort", $order)
            ->getQuery()
            ->getResult();
    }
}
