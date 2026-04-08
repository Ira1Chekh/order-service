<?php

namespace App\Tests\Service;

use Acme\SharedBundle\DTO\OrderDTO;
use App\Entity\LocalProduct;
use App\Entity\Order;
use App\Enum\OrderStatus;
use App\Exception\InsufficientStockException;
use App\Exception\ProductNotFoundException;
use App\Repository\LocalProductRepository;
use App\Repository\OrderRepository;
use App\Service\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class OrderServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private LocalProductRepository&MockObject $localProductRepository;
    private OrderRepository&MockObject $orderRepository;
    private MessageBusInterface&MockObject $bus;
    private OrderService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->localProductRepository = $this->createMock(LocalProductRepository::class);
        $this->orderRepository = $this->createMock(OrderRepository::class);
        $this->bus = $this->createMock(MessageBusInterface::class);

        $this->service = new OrderService(
            $this->em,
            $this->localProductRepository,
            $this->orderRepository,
            $this->bus,
        );
    }

    public function testCreateThrowsProductNotFoundExceptionWhenProductDoesNotExist(): void
    {
        $this->localProductRepository
            ->method('findWithLock')
            ->with('unknown-id')
            ->willReturn(null);

        $this->em->expects($this->once())->method('beginTransaction');
        $this->em->expects($this->once())->method('rollback');
        $this->em->expects($this->never())->method('commit');

        $this->expectException(ProductNotFoundException::class);
        $this->expectExceptionMessage('unknown-id');

        $this->service->create('unknown-id', 'John Doe', 2);
    }

    public function testCreateThrowsInsufficientStockExceptionWhenStockTooLow(): void
    {
        $product = $this->makeProduct(quantity: 3);

        $this->localProductRepository
            ->method('findWithLock')
            ->willReturn($product);

        $this->em->expects($this->once())->method('beginTransaction');
        $this->em->expects($this->once())->method('rollback');
        $this->em->expects($this->never())->method('commit');

        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionMessage('Available: 3, requested: 5');

        $this->service->create('product-id', 'John Doe', 5);
    }

    public function testCreateDeductsStockAndReturnsOrder(): void
    {
        $product = $this->makeProduct(quantity: 10);

        $this->localProductRepository
            ->method('findWithLock')
            ->willReturn($product);

        $this->em->expects($this->once())->method('beginTransaction');
        $this->em->expects($this->once())->method('persist')->with($this->isInstanceOf(Order::class));
        $this->em->expects($this->once())->method('flush');
        $this->em->expects($this->once())->method('commit');
        $this->em->expects($this->never())->method('rollback');

        $this->bus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(OrderDTO::class))
            ->willReturn(new Envelope(new OrderDTO('', new \Acme\SharedBundle\DTO\ProductDTO('', '', 0, 0), '', 0, '')));

        $order = $this->service->create('product-id', 'John Doe', 3);

        $this->assertSame(7, $product->getQuantity());
        $this->assertSame('John Doe', $order->getCustomerName());
        $this->assertSame(3, $order->getQuantityOrdered());
        $this->assertSame(OrderStatus::Success, $order->getOrderStatus());
        $this->assertSame($product, $order->getProduct());
    }

    public function testCreateRollsBackOnUnexpectedException(): void
    {
        $this->localProductRepository
            ->method('findWithLock')
            ->willThrowException(new \RuntimeException('DB failure'));

        $this->em->expects($this->once())->method('beginTransaction');
        $this->em->expects($this->once())->method('rollback');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DB failure');

        $this->service->create('product-id', 'John Doe', 1);
    }

    public function testFindAllDelegatesToRepository(): void
    {
        $orders = [$this->createMock(Order::class), $this->createMock(Order::class)];

        $this->orderRepository->method('findAll')->willReturn($orders);

        $this->assertSame($orders, $this->service->findAll());
    }

    public function testFindByIdReturnsOrderWhenFound(): void
    {
        $order = $this->createMock(Order::class);

        $this->orderRepository->method('find')->with('some-id')->willReturn($order);

        $this->assertSame($order, $this->service->findById('some-id'));
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        $this->orderRepository->method('find')->with('missing-id')->willReturn(null);

        $this->assertNull($this->service->findById('missing-id'));
    }

    // --- helpers ---

    private function makeProduct(int $quantity): LocalProduct
    {
        $product = new LocalProduct();
        $product->setName('Coffee Mug');
        $product->setPrice(12.99);
        $product->setQuantity($quantity);

        return $product;
    }
}
