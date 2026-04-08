<?php

namespace App\Service;

use App\Entity\Product;

interface ProductServiceInterface
{
    public function create(string $name, float $price, int $quantity): Product;

    /** @return Product[] */
    public function findAll(): array;

    public function findById(string $id): ?Product;
}
