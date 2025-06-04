<?php

namespace App\Controller;

use App\Enum\ProductSize;
use App\Repository\CategoryRepository;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/products')]
class ProductController extends AbstractController
{
    public function __construct(private ProductService $productService) {}


    #[Route('/categories', name: 'api_categories_list', methods: ['GET'])]
    public function listCategories(CategoryRepository $categoryRepository): JsonResponse
    {
        $categories = $categoryRepository->findAll();

        // Преобразуем объекты Category в массивы для JSON-ответа
        $data = array_map(static function($category) {
            return [
                'id' => $category->getId(),
                'nameCategory' => $category->getNameCategory(),
                'descriptionCategory' => $category->getDescriptionCategory(),
            ];
        }, $categories);

        return $this->json($data);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // Проверка массива размеров
        if (
            !isset($data['size']) ||
            !is_array($data['size']) ||
            array_diff($data['size'], ProductSize::values()) !== []
        ) {
            return $this->json([
                'error' => 'Недопустимые размеры. Разрешённые значения: ' . implode(', ', ProductSize::values()),
                'allowed_sizes' => ProductSize::values(),
            ], 400);
        }

        $product = $this->productService->createProduct($data);




        return $this->json($product, 201,[], ['groups' => 'product:read']);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $product = $this->productService->getProduct($id);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        return $this->json($product,200,[], ['groups' => 'product:read']);
    }

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $sortField = $request->query->get('sort', 'id');
        $sortOrder = $request->query->get('order', 'asc');
        $search = $request->query->get('search');
        $categoryId = $request->query->get('category');

        try {
            $products = $this->productService->getAllProducts($sortField, $sortOrder, $search, $categoryId);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        return $this->json($products, 200, [], ['groups' => 'product:read']);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if (isset($data['size'])) {
            if (
                !is_array($data['size']) ||
                array_diff($data['size'], ProductSize::values()) !== []
            ) {
                return $this->json([
                    'error' => 'Недопустимые размеры. Разрешённые значения: ' . implode(', ', ProductSize::values()),
                    'allowed_sizes' => ProductSize::values(),
                ], 400);
            }
        }

        $product = $this->productService->updateProduct($id, $data);

        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        return $this->json($product, 201,[], ['groups' => 'product:read']);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $success = $this->productService->deleteProduct($id);
        if (!$success) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        return $this->json(['message' => 'Product deleted successfully']);
    }

    #[Route('/sizes', methods: ['GET'])]
    public function getAvailableSizes(): JsonResponse
    {
        return $this->json([
            'available_sizes' => ProductSize::values(),
        ]);
    }


}
