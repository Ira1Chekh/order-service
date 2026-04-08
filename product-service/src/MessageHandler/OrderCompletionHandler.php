<?php

namespace App\MessageHandler;

use Acme\SharedBundle\DTO\OrderDTO;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class OrderCompletionHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProductRepository $productRepository,
    ) {
    }

    public function __invoke(OrderDTO $dto): void
    {
        $this->em->beginTransaction();

        try {
            $product = $this->productRepository->findWithLock($dto->product->id);

            if (!$product) {
                $this->em->rollback();
                return;
            }

            $product->setQuantity(max(0, $product->getQuantity() - $dto->quantityOrdered));

            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }
}
