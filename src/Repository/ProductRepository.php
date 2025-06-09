<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function getAllProducts(
        string $sortField = 'id',
        string $sortOrder = 'asc',
        ?string $search = null,
        ?int $categoryId = null,
        ?int $minRating = null,
        ?int $minTone = null
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.reviews', 'r')
            ->addSelect('AVG(r.rating) as avgRating')
            ->addSelect('AVG(r.numberTone) as avgTone')
            ->groupBy('p.id');

        if ($search) {
            $qb->andWhere('p.nameProduct LIKE :search OR p.descriptionProduct LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($categoryId) {
            $qb->andWhere('p.category = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if ($minRating !== null || $minTone !== null) {
            $having = [];

            if ($minRating !== null) {
                if ($minRating === -1) {
                    $having[] = 'AVG(r.rating) = 0';
                } elseif ($minRating === 1) {
                    $having[] = 'AVG(r.rating) > 0';
                } else {
                    $having[] = 'AVG(r.rating) >= :minRating';
                    $qb->setParameter('minRating', $minRating);
                }
            }

            if ($minTone !== null) {
                if ($minTone === -1) {
                    $having[] = 'AVG(r.numberTone) = 0';
                } elseif ($minTone === 1) {
                    $having[] = 'AVG(r.numberTone) > 0';
                } else {
                    $having[] = 'AVG(r.numberTone) >= :minTone';
                    $qb->setParameter('minTone', $minTone);
                }
            }

            $qb->having(implode(' AND ', $having));
        }

        $results = $qb->getQuery()->getResult();

        // Обновляем объекты Product
        foreach ($results as $row) {
            /** @var Product $product */
            $product = $row[0];
            $product->setAvgRating(round((float) $row['avgRating'], 2));
            $product->setAvgTone(round((float) $row['avgTone'], 2));
        }

        return array_column($results, 0);
    }
}
