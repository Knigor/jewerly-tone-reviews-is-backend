<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\Review;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReviewService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ReviewRepository $reviewRepository,
        private ProductRepository $productRepository,
    ) {}

    public function createReview(array $data): Review
    {
        $review = new Review();

        if (!isset($data['textReview'], $data['productId'])) {
            throw new InvalidArgumentException('textReview, numberTone and productId are required.');
        }

        $review->setTextReview($data['textReview']);
        $review->setNumberTone(0);
        $review->setRating($data['rating']);
        $review->setCreatedAt(new \DateTimeImmutable());
        $review->setIsModerated($data['isModerated'] ?? false);

        $product = $this->productRepository->find($data['productId']);
        if (!$product) {
            throw new NotFoundHttpException('Product not found with ID ' . $data['productId']);
        }

        $review->setProductId($product);

        $this->em->persist($review);
        $this->em->flush();

        return $review;
    }

    public function moderateReview(int $id): ?Review
    {
        $review = $this->reviewRepository->find($id);
        if (!$review) {
            return null;
        }

        $review->setIsModerated(true);
        $this->em->flush();

        return $review;
    }



    public function updateReview(int $id, array $data): ?Review
    {
        $review = $this->reviewRepository->find($id);
        if (!$review) {
            return null;
        }

        if (isset($data['textReview'])) {
            $review->setTextReview($data['textReview']);
        }
        if (isset($data['numberTone'])) {
            $review->setNumberTone((int)$data['numberTone']);
        }
        if (isset($data['isModerated'])) {
            $review->setIsModerated((bool)$data['isModerated']);
        }
        if (isset($data['productId'])) {
            $product = $this->productRepository->find($data['productId']);
            if (!$product) {
                throw new NotFoundHttpException('Product not found with ID ' . $data['productId']);
            }
            $review->setProductId($product);
        }

        $this->em->flush();

        return $review;
    }

    public function deleteReview(int $id): bool
    {
        $review = $this->reviewRepository->find($id);
        if (!$review) {
            return false;
        }

        $this->em->remove($review);
        $this->em->flush();

        return true;
    }

    public function getReview(int $id): ?Review
    {
        return $this->reviewRepository->find($id);
    }

    public function getAllReviews(): array
    {
        return $this->reviewRepository->findAll();
    }
}
