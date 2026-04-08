<?php

namespace App\Service;

use Acme\SharedBundle\DTO\OrderDTO;
use Acme\SharedBundle\DTO\ProductDTO;
use App\Entity\Order;
use App\Enum\OrderStatus;
use App\Exception\InsufficientStockException;
use App\Exception\ProductNotFoundException;
use App\Repository\LocalProductRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class OrderService implements OrderServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LocalProductRepository $localProductRepository,
        private readonly OrderRepository $orderRepository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function create(string $productId, string $customerName, int $quantityOrdered): Order
    {
        $this->em->beginTransaction();

        try {
            $product = $this->localProductRepository->findWithLock($productId);

            if (!$product) {
                throw new ProductNotFoundException($productId);
            }

            if ($product->getQuantity() < $quantityOrdered) {
                throw new InsufficientStockException($product->getQuantity(), $quantityOrdered);
            }

            $product->setQuantity($product->getQuantity() - $quantityOrdered);

            $order = new Order();
            $order->setProduct($product);
            $order->setCustomerName($customerName);
            $order->setQuantityOrdered($quantityOrdered);
            $order->setOrderStatus(OrderStatus::Processing);

            $this->em->persist($order);
            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }

        $this->bus->dispatch(new OrderDTO(
            $order->getOrderId(),
            new ProductDTO(
                $product->getId(),
                $product->getName(),
                $product->getPrice(),
                $product->getQuantity(),
            ),
            $order->getCustomerName(),
            $order->getQuantityOrdered(),
            $order->getOrderStatus()->value,
        ));

        return $order;
    }

    /** @return Order[] */
    public function findAll(): array
    {
        return $this->orderRepository->findAll();
    }

    public function findById(string $id): ?Order
    {
        return $this->orderRepository->find($id);
    }
}
