<?php

namespace Acme\SharedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\MappedSuperclass]
abstract class BaseProduct
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    protected string $id;

    #[ORM\Column(type: 'string', length: 255)]
    protected string $name;

    #[ORM\Column(type: 'float')]
    protected float $price;

    #[ORM\Column(type: 'integer')]
    protected int $quantity;

    public function __construct(?string $id = null)
    {
        $this->id = $id ?? Uuid::v4()->toRfc4122();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }
}
