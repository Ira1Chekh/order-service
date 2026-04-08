<?php

namespace App\MessageHandler;

use Acme\SharedBundle\DTO\OrderDTO;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class OrderCompletionHandler
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function __invoke(OrderDTO $dto): void
    {
        $order = $this->orderRepository->find($dto->orderId);

        if (!$order) {
            return;
        }

        $order->setOrderStatus(OrderStatus::Success);
        $this->em->flush();
    }
}
