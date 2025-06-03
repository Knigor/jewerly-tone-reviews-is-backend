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

        $qb->orderBy('p.' . $sortField, $sortOrder);

        return $qb->getQuery()->getResult();
    }
}
