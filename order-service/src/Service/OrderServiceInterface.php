<?php

namespace App\Service;

use App\Entity\Order;

interface OrderServiceInterface
{
    public function create(string $productId, string $customerName, int $quantityOrdered): Order;

    /** @return Order[] */
    public function findAll(): array;

    public function findById(string $id): ?Order;
}
