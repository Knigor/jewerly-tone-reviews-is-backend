<?php

namespace App\Controller;

use App\Entity\OrderItems;
use App\Entity\Orders;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\OrdersRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/orders')]
class OrderReviewController extends AbstractController
{
    #[Route('/', name: 'api_get_orders', methods: ['GET'])]
    public function getOrders(OrdersRepository $ordersRepository): JsonResponse
    {
        $orders = $ordersRepository->findBy([], ['id' => 'ASC']);
        $ordersData = [];

        foreach ($orders as $order) {
            $ordersData[] = $this->serializeOrder($order);
        }

        return $this->json($ordersData);
    }

    #[Route('/{id}', name: 'api_get_order', methods: ['GET'])]
    public function getOrder(Orders $order): JsonResponse
    {
        return $this->json($this->serializeOrder($order));
    }

    #[Route('/', name: 'api_create_order', methods: ['POST'])]
    public function createOrder(
        Request $request,
        EntityManagerInterface $em,
        ProductRepository $productRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['userId'], $data['items']) || !is_array($data['items'])) {
            return $this->json(['message' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }

        $user = $em->getRepository(User::class)->find($data['userId']);
        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $moscowTimeZone = new \DateTimeZone('Europe/Moscow');

        $order = new Orders();
        $order->setCreatedAt(new \DateTimeImmutable('now', $moscowTimeZone));
        $order->setStatusOrder('На рассмотрении');
        $order->setEmailOrder($user->getEmail());
        $order->setUsers($user);

        $totalQuantity = 0;
        foreach ($data['items'] as $itemData) {
            if (!isset($itemData['productId'], $itemData['quantity'])) {
                continue;
            }

            $product = $productRepository->find($itemData['productId']);
            if (!$product) {
                continue;
            }

            $orderItem = new OrderItems();
            $orderItem->setProductId($product);
            $orderItem->setQuantity($itemData['quantity']);
            $orderItem->setPriceOrder($product->getPriceProduct());
            $orderItem->setOrderId($order);

            $em->persist($orderItem);
            $order->addOrderItem($orderItem);

            $totalQuantity += $itemData['quantity'];
        }

        $order->setAmountQuantity($totalQuantity);
        $em->persist($order);
        $em->flush();

        return $this->json($this->serializeOrder($order), Response::HTTP_CREATED);
    }

    #[Route('/{id}/status', name: 'api_update_order_status', methods: ['PATCH'])]
    public function updateOrderStatus(Orders $order, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['status'])) {
            return $this->json(['message' => 'Status is required'], Response::HTTP_BAD_REQUEST);
        }

        $order->setStatusOrder($data['status']);
        $em->flush();

        return $this->json($this->serializeOrder($order));
    }

    #[Route('/user/{userId}', name: 'api_get_user_orders', methods: ['GET'])]
    public function getUserOrders(int $userId, OrdersRepository $ordersRepository): JsonResponse
    {
        $orders = $ordersRepository->findBy(['users' => $userId]);
        $ordersData = [];

        foreach ($orders as $order) {
            $ordersData[] = $this->serializeOrder($order);
        }

        return $this->json($ordersData);
    }

    #[Route('/{id}/items', name: 'api_add_order_item', methods: ['POST'])]
    public function addOrderItem(
        Orders $order,
        Request $request,
        EntityManagerInterface $em,
        ProductRepository $productRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['productId'], $data['quantity'])) {
            return $this->json(['message' => 'Product ID and quantity are required'], Response::HTTP_BAD_REQUEST);
        }

        $product = $productRepository->find($data['productId']);
        if (!$product) {
            return $this->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $orderItem = new OrderItems();
        $orderItem->setProductId($product);
        $orderItem->setQuantity($data['quantity']);
        $orderItem->setPriceOrder($product->getPriceProduct());
        $orderItem->setOrderId($order);

        $em->persist($orderItem);
        $order->addOrderItem($orderItem);
        $order->setAmountQuantity($order->getAmountQuantity() + $data['quantity']);
        $em->flush();

        return $this->json($this->serializeOrder($order), Response::HTTP_CREATED);
    }

    #[Route('/items/{id}', name: 'api_remove_order_item', methods: ['DELETE'])]
    public function removeOrderItem(OrderItems $orderItem, EntityManagerInterface $em): JsonResponse
    {
        $order = $orderItem->getOrderId();
        $order->setAmountQuantity($order->getAmountQuantity() - $orderItem->getQuantity());

        $em->remove($orderItem);
        $em->flush();

        return $this->json(['message' => 'Order item removed successfully']);
    }

    private function serializeOrder(Orders $order): array
    {
        $orderItems = [];
        $totalAmount = 0;

        foreach ($order->getOrderItems() as $item) {
            $itemTotal = $item->getQuantity() * $item->getPriceOrder();
            $orderItems[] = [
                'id' => $item->getId(),
                'productId' => $item->getProductId()->getId(),
                'productName' => $item->getProductId()->getNameProduct(),
                'quantity' => $item->getQuantity(),
                'price' => $item->getPriceOrder(),
                'total' => $itemTotal,
            ];
            $totalAmount += $itemTotal;
        }

        $user = $order->getUsers();

        return [
            'id' => $order->getId(),
            'createdAt' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
            'status' => $order->getStatusOrder(),
            'totalQuantity' => $order->getAmountQuantity(),
            'totalAmount' => $totalAmount,
            'userId' => $user ? $user->getId() : null,
            'userEmail' => $user ? $user->getEmail() : null,
            'userName' => $user ? $user->getFullName() : null,
            'items' => $orderItems,
        ];
    }
}
