<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\ProductServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/products')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductServiceInterface $productService,
    ) {
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($data['name']) || !isset($data['price'], $data['quantity'])) {
            return $this->json(['error' => 'name, price and quantity are required'], Response::HTTP_BAD_REQUEST);
        }

        $product = $this->productService->create(
            $data['name'],
            (float) $data['price'],
            (int) $data['quantity'],
        );

        return $this->json($this->serialize($product), Response::HTTP_CREATED);
    }

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $products = $this->productService->findAll();

        return $this->json([
            'data' => array_map(fn(Product $p) => $this->serialize($p), $products),
        ]);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $product = $this->productService->findById($id);

        if (!$product) {
            return $this->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->serialize($product));
    }

    private function serialize(Product $product): array
    {
        return [
            'id'       => $product->getId(),
            'name'     => $product->getName(),
            'price'    => $product->getPrice(),
            'quantity' => $product->getQuantity(),
        ];
    }
}
