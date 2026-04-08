<?php

namespace App\Tests\MessageHandler;

use Acme\SharedBundle\DTO\OrderDTO;
use Acme\SharedBundle\DTO\ProductDTO;
use App\Entity\Order;
use App\Enum\OrderStatus;
use App\MessageHandler\OrderCompletionHandler;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderCompletionHandlerTest extends TestCase
{
    private OrderRepository&MockObject $orderRepository;
    private EntityManagerInterface&MockObject $em;
    private OrderCompletionHandler $handler;

    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(OrderRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->handler = new OrderCompletionHandler($this->orderRepository, $this->em);
    }

    public function testSetsOrderStatusToSuccess(): void
    {
        $order = new Order();
        $order->setOrderStatus(OrderStatus::Processing);

        $dto = new OrderDTO(
            orderId: 'order-uuid',
            product: new ProductDTO('product-uuid', 'Coffee Mug', 12.99, 8),
            customerName: 'John Doe',
            quantityOrdered: 2,
            orderStatus: OrderStatus::Processing->value,
        );

        $this->orderRepository->method('find')->with('order-uuid')->willReturn($order);
        $this->em->expects($this->once())->method('flush');

        ($this->handler)($dto);

        $this->assertSame(OrderStatus::Success, $order->getOrderStatus());
    }

    public function testDoesNothingWhenOrderNotFound(): void
    {
        $dto = new OrderDTO(
            orderId: 'missing-uuid',
            product: new ProductDTO('product-uuid', 'Coffee Mug', 12.99, 8),
            customerName: 'John Doe',
            quantityOrdered: 2,
            orderStatus: OrderStatus::Processing->value,
        );

        $this->orderRepository->method('find')->with('missing-uuid')->willReturn(null);
        $this->em->expects($this->never())->method('flush');

        ($this->handler)($dto);
    }
}
