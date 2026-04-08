<?php

namespace App\MessageHandler;

use Acme\SharedBundle\DTO\OrderDTO;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class OrderCompletionHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(OrderDTO $dto): void
    {
        $this->logger->info('Order completed', [
            'orderId'         => $dto->orderId,
            'customerName'    => $dto->customerName,
            'quantityOrdered' => $dto->quantityOrdered,
            'orderStatus'     => $dto->orderStatus,
        ]);
    }
}
