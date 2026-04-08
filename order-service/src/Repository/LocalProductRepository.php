<?php

namespace App\Repository;

use App\Entity\LocalProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;

class LocalProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocalProduct::class);
    }

    public function findWithLock(string $id): ?LocalProduct
    {
        return $this->getEntityManager()->find(LocalProduct::class, $id, LockMode::PESSIMISTIC_WRITE);
    }
}
