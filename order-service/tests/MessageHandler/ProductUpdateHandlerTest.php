<?php

namespace App\Tests\MessageHandler;

use Acme\SharedBundle\DTO\ProductDTO;
use App\Entity\LocalProduct;
use App\MessageHandler\ProductUpdateHandler;
use App\Repository\LocalProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductUpdateHandlerTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private LocalProductRepository&MockObject $repository;
    private ProductUpdateHandler $handler;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(LocalProductRepository::class);
        $this->handler = new ProductUpdateHandler($this->em, $this->repository);
    }

    public function testCreatesNewLocalProductWhenNotFound(): void
    {
        $dto = new ProductDTO('new-uuid', 'Coffee Mug', 12.99, 100);

        $this->repository->method('find')->with('new-uuid')->willReturn(null);

        $this->em
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (LocalProduct $p) use ($dto) {
                return $p->getId() === $dto->id
                    && $p->getName() === $dto->name
                    && $p->getPrice() === $dto->price
                    && $p->getQuantity() === $dto->quantity;
            }));

        $this->em->expects($this->once())->method('flush');

        ($this->handler)($dto);
    }

    public function testHandlesDuplicateMessageIdempotently(): void
    {
        $existing = new LocalProduct();
        $existing->setName('Coffee Mug');
        $existing->setPrice(12.99);
        $existing->setQuantity(10);

        $dto = new ProductDTO('existing-uuid', 'Coffee Mug', 12.99, 100);

        $this->repository->method('find')->with('existing-uuid')->willReturn($existing);

        $this->em->expects($this->once())->method('persist')->with($existing);
        $this->em->expects($this->once())->method('flush');

        ($this->handler)($dto);

        // Existing products are not modified — no name, price, or quantity updates.
        // Product-service has no update endpoint so redelivered messages carry identical data.
        $this->assertSame('Coffee Mug', $existing->getName());
        $this->assertSame(12.99, $existing->getPrice());
        $this->assertSame(10, $existing->getQuantity());
    }

    public function testDoesNotOverwriteIdOnExistingProduct(): void
    {
        $existing = new LocalProduct();
        $existing->setName('Mug');
        $existing->setPrice(10.0);
        $existing->setQuantity(5);
        $originalId = $existing->getId();

        $dto = new ProductDTO($originalId, 'Mug Updated', 11.0, 8);

        $this->repository->method('find')->willReturn($existing);
        $this->em->method('persist');
        $this->em->method('flush');

        ($this->handler)($dto);

        $this->assertSame($originalId, $existing->getId());
    }
}
