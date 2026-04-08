<?php

namespace App\Controller;

use App\Entity\Order;
use App\Exception\InsufficientStockException;
use App\Exception\ProductNotFoundException;
use App\Service\OrderServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/orders')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderServiceInterface $orderService,
    ) {
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($data['productId']) || empty($data['customerName']) || !isset($data['quantityOrdered'])) {
            return $this->json(
                ['error' => 'productId, customerName and quantityOrdered are required'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $quantityOrdered = (int) $data['quantityOrdered'];
        if ($quantityOrdered < 1) {
            return $this->json(['error' => 'quantityOrdered must be at least 1'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $order = $this->orderService->create(
                $data['productId'],
                $data['customerName'],
                $quantityOrdered,
            );
        } catch (ProductNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (InsufficientStockException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->json($this->serialize($order), Response::HTTP_CREATED);
    }

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $orders = $this->orderService->findAll();

        return $this->json([
            'data' => array_map(fn(Order $o) => $this->serialize($o), $orders),
        ]);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $order = $this->orderService->findById($id);

        if (!$order) {
            return $this->json(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->serialize($order));
    }

    private function serialize(Order $order): array
    {
        $product = $order->getProduct();

        return [
            'orderId'         => $order->getOrderId(),
            'product'         => [
                'id'       => $product->getId(),
                'name'     => $product->getName(),
                'price'    => $product->getPrice(),
                'quantity' => $product->getQuantity(),
            ],
            'customerName'    => $order->getCustomerName(),
            'quantityOrdered' => $order->getQuantityOrdered(),
            'orderStatus'     => $order->getOrderStatus()->value,
        ];
    }
}
