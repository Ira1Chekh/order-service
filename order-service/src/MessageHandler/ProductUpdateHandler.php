<?php

namespace App\MessageHandler;

use Acme\SharedBundle\DTO\ProductDTO;
use App\Entity\LocalProduct;
use App\Repository\LocalProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProductUpdateHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LocalProductRepository $localProductRepository,
    ) {
    }

    public function __invoke(ProductDTO $dto): void
    {
        $product = $this->localProductRepository->find($dto->id);

        if (!$product) {
            $product = new LocalProduct($dto->id);
            // Quantity is only trusted from the product-service on initial sync.
            // After that, the order-service manages it locally to prevent stale
            // ProductDTO messages from overwriting quantity decrements made by orders.
            $product->setQuantity($dto->quantity);
        }

        $product->setName($dto->name);
        $product->setPrice($dto->price);

        $this->em->persist($product);
        $this->em->flush();
    }
}
