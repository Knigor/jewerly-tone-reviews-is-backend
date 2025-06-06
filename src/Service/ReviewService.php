<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\Review;
use App\Entity\User;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ReviewService
{


    public function __construct(
        private EntityManagerInterface $em,
        private ReviewRepository $reviewRepository,
        private ProductRepository $productRepository,
        private ToneAnalyzerService $toneAnalyzer,
        private UserRepository $userRepository,
    ) {}

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function createReview(array $data): Review
    {
        if (!isset($data['textReview'], $data['productId'], $data['userId'])) {
            throw new InvalidArgumentException('textReview, productId and userId are required.');
        }

        $product = $this->productRepository->find($data['productId']);
        if (!$product) {
            throw new NotFoundHttpException('Product not found with ID ' . $data['productId']);
        }

        $user = $this->userRepository->find($data['userId']);
        if (!$user) {
            throw new NotFoundHttpException('User not found with ID ' . $data['userId']);
        }

        $review = new Review();
        $review->setTextReview($data['textReview']);
        $review->setRating((int)($data['rating'] ?? 0));
        $review->setCreatedAt(new \DateTimeImmutable());
        $review->setIsModerated($data['isModerated'] ?? false);
        $review->setProductId($product);
        $review->setUser($user);

        $tone = $this->toneAnalyzer->analyze($data['textReview']);
        $review->setNumberTone($tone ?? 0);

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

    public function getAllReviews(string $sort = 'createdAt', string $order = 'DESC'): array
    {
        return $this->reviewRepository->findAllSorted($sort, $order);
    }

    public function getModeratedReviewsByProduct(int $productId): array
    {
        $product = $this->productRepository->find($productId);

        if (!$product) {
            throw new NotFoundHttpException("Product with ID $productId not found.");
        }

        return $this->reviewRepository->findBy([
            'productId' => $product,
            'isModerated' => true
        ], [
            'createdAt' => 'DESC'
        ]);
    }
}
