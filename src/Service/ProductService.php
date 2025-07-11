<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\User;
use App\Entity\Category;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use App\Enum\ProductSize;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ProductRepository $productRepository
    ) {}

    public function createProduct(array $data): Product
    {

        $product = new Product();
        $product->setNameProduct($data['name']);
        $product->setDescriptionProduct($data['description']);
        $product->setPriceProduct($data['price']);
        $product->setMetal($data['metal']);
        $product->setImgUrlProduct($data['img_url']);

        if (!isset($data['size'])) {
            throw new InvalidArgumentException('Size is required.');
        }

        $sizeArray = $data['size']; // массив int

        if (!isset($data['category_id'])) {
            throw new InvalidArgumentException('category_id is required.');
        }

        $category = $this->em->getRepository(Category::class)->find($data['category_id']);

        if (!$category) {
            throw new NotFoundHttpException('Category not found with ID ' . $data['category_id']);
        }

        $product->setCategory($category);


        $product->setSizeProduct($sizeArray);



        if (!isset($data['user_id'])) {
            throw new InvalidArgumentException('user_id is required.');
        }

        $user = $this->em->getRepository(User::class)->find($data['user_id']);

        if (!$user) {
            throw new NotFoundHttpException('User not found with ID ' . $data['user_id']);
        }

        $product->setUserId($user);

        $this->em->persist($product);
        $this->em->flush();

        return $product;
    }

    public function updateProduct(int $id, array $data): ?Product
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            return null;
        }

        if (isset($data['name'])) {
            $product->setNameProduct($data['name']);
        }
        if (isset($data['description'])) {
            $product->setDescriptionProduct($data['description']);
        }
        if (isset($data['price'])) {
            $product->setPriceProduct($data['price']);
        }
        if (isset($data['metal'])) {
            $product->setMetal($data['metal']);
        }
        if (isset($data['size'])) {
            $product->setSizeProduct($data['size']);
        }
        if (isset($data['img_url'])) {
            $product->setImgUrlProduct($data['img_url']);
        }


        if (!isset($data['category_id'])) {
            throw new InvalidArgumentException('category_id is required.');
        }

        $category = $this->em->getRepository(Category::class)->find($data['category_id']);

        if (!$category) {
            throw new NotFoundHttpException('Category not found with ID ' . $data['category_id']);
        }

        $product->setCategory($category);

        $this->em->flush();

        return $product;
    }

    public function deleteProduct(int $id): bool
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            return false;
        }

        $this->em->remove($product);
        $this->em->flush();

        return true;
    }

    public function getProduct(int $id): ?Product
    {
        return $this->productRepository->find($id);
    }

    public function getAllProducts(
        string $sortField,
        string $sortOrder,
        ?string $search,
        ?int $categoryId,
        ?int $minRating,
        ?int $minTone
    ): array {
        return $this->productRepository->getAllProducts($sortField, $sortOrder, $search, $categoryId, $minRating, $minTone);
    }



}