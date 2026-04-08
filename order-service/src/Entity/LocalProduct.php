<?php

namespace App\Entity;

use Acme\SharedBundle\Entity\BaseProduct;
use App\Repository\LocalProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocalProductRepository::class)]
#[ORM\Table(name: 'local_products')]
class LocalProduct extends BaseProduct
{
}
