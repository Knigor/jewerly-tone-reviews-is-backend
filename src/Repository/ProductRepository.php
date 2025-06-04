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

    public function findBySearchQuery(string $search, string $sortField = 'id', string $sortOrder = 'asc'): array
    {
        $qb = $this->createQueryBuilder('p');

        $qb->where('LOWER(p.nameProduct) LIKE :search')
            ->orWhere('LOWER(p.descriptionProduct) LIKE :search')
            ->setParameter('search', '%' . mb_strtolower($search) . '%');

        $allowedFields = ['id', 'nameProduct', 'priceProduct', 'metal'];
        $allowedOrders = ['asc', 'desc'];

        if (!in_array($sortField, $allowedFields, true)) {
            $sortField = 'id';
        }

        if (!in_array(strtolower($sortOrder), $allowedOrders, true)) {
            $sortOrder = 'asc';
        }

        if ($sortField === 'category') {
            $qb->leftJoin('p.category', 'c')
                ->addSelect('c')
                ->orderBy('c.name', $sortOrder);
        } else {
            $qb->orderBy('p.' . $sortField, $sortOrder);
        }

        $qb->orderBy('p.' . $sortField, $sortOrder);

        return $qb->getQuery()->getResult();
    }

    public function findSortedByCategory(string $sortOrder = 'asc', ?string $search = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->orderBy('c.name', $sortOrder);

        if ($search !== null && trim($search) !== '') {
            $qb->andWhere('LOWER(p.nameProduct) LIKE :search OR LOWER(p.descriptionProduct) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower($search) . '%');
        }

        return $qb->getQuery()->getResult();
    }


    public function findFilteredProducts(
        ?string $search = null,
        string $sortField = 'id',
        string $sortOrder = 'asc',
        ?int $categoryId = null
    ): array {
        $qb = $this->createQueryBuilder('p');

        if ($sortField === 'category') {
            $qb->leftJoin('p.category', 'c')
                ->addSelect('c')
                ->orderBy('c.name', $sortOrder);
        } else {
            $qb->orderBy('p.' . $sortField, $sortOrder);
        }

        if ($search !== null && trim($search) !== '') {
            $qb->andWhere('LOWER(p.nameProduct) LIKE :search OR LOWER(p.descriptionProduct) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower($search) . '%');
        }

        if ($categoryId !== null) {
            $qb->andWhere('p.category = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        return $qb->getQuery()->getResult();
    }


}
