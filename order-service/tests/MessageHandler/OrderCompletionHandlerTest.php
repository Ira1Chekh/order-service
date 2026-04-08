<?php

namespace App\Tests\MessageHandler;

use Acme\SharedBundle\DTO\OrderDTO;
use Acme\SharedBundle\DTO\ProductDTO;
use App\MessageHandler\OrderCompletionHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class OrderCompletionHandlerTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private OrderCompletionHandler $handler;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->handler = new OrderCompletionHandler($this->logger);
    }

    public function testLogsOrderCompletion(): void
    {
        $dto = new OrderDTO(
            orderId: 'order-uuid',
            product: new ProductDTO('product-uuid', 'Coffee Mug', 12.99, 8),
            customerName: 'John Doe',
            quantityOrdered: 2,
            orderStatus: 'Success',
        );

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Order completed', [
                'orderId'         => 'order-uuid',
                'customerName'    => 'John Doe',
                'quantityOrdered' => 2,
                'orderStatus'     => 'Success',
            ]);

        ($this->handler)($dto);
    }
}
