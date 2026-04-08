<?php

namespace App\Entity;

use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
class Order
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private string $orderId;

    #[ORM\ManyToOne(targetEntity: LocalProduct::class)]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false)]
    private LocalProduct $product;

    #[ORM\Column(type: 'string', length: 255)]
    private string $customerName;

    #[ORM\Column(type: 'integer')]
    private int $quantityOrdered;

    #[ORM\Column(type: 'string', length: 20, enumType: OrderStatus::class)]
    private OrderStatus $orderStatus;

    public function __construct()
    {
        $this->orderId = Uuid::v4()->toRfc4122();
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getProduct(): LocalProduct
    {
        return $this->product;
    }

    public function setProduct(LocalProduct $product): static
    {
        $this->product = $product;
        return $this;
    }

    public function getCustomerName(): string
    {
        return $this->customerName;
    }

    public function setCustomerName(string $customerName): static
    {
        $this->customerName = $customerName;
        return $this;
    }

    public function getQuantityOrdered(): int
    {
        return $this->quantityOrdered;
    }

    public function setQuantityOrdered(int $quantityOrdered): static
    {
        $this->quantityOrdered = $quantityOrdered;
        return $this;
    }

    public function getOrderStatus(): OrderStatus
    {
        return $this->orderStatus;
    }

    public function setOrderStatus(OrderStatus $orderStatus): static
    {
        $this->orderStatus = $orderStatus;
        return $this;
    }
}
