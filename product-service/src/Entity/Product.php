<?php

namespace App\Entity;

use Acme\SharedBundle\Entity\BaseProduct;
use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'products')]
class Product extends BaseProduct
{
}
