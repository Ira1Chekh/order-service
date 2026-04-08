<?php

namespace App\Service;

use Acme\SharedBundle\DTO\ProductDTO;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ProductService implements ProductServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProductRepository $productRepository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function create(string $name, float $price, int $quantity): Product
    {
        $product = new Product();
        $product->setName($name);
        $product->setPrice($price);
        $product->setQuantity($quantity);

        $this->em->persist($product);
        $this->em->flush();

        $this->bus->dispatch(new ProductDTO(
            $product->getId(),
            $product->getName(),
            $product->getPrice(),
            $product->getQuantity(),
        ));

        return $product;
    }

    /** @return Product[] */
    public function findAll(): array
    {
        return $this->productRepository->findAll();
    }

    public function findById(string $id): ?Product
    {
        return $this->productRepository->find($id);
    }
}
