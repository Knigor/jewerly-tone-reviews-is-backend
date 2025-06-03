<?php

namespace App\Controller;

use App\Service\ReviewService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/reviews')]
class ReviewController extends AbstractController
{
    public function __construct(private ReviewService $reviewService) {}

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        try {
            $review = $this->reviewService->createReview($data);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }

        return $this->json(['message' => 'Review created successfully'], 201, [], ['groups' => 'review:read']);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $review = $this->reviewService->getReview($id);
        if (!$review) {
            return $this->json(['error' => 'Review not found'], 404);
        }

        return $this->json($review, 200, [], ['groups' => 'review:read']);
    }

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $sort = $request->query->get('sort', 'createdAt');
        $order = $request->query->get('order', 'DESC');

        $reviews = $this->reviewService->getAllReviews($sort, $order);

        return $this->json($reviews, 200, [], ['groups' => 'review:read']);
    }


    #[Route('/{id}/moderate', methods: ['PUT'])]
    public function moderate(int $id): JsonResponse
    {
        $review = $this->reviewService->moderateReview($id);

        if (!$review) {
            return $this->json(['error' => 'Review not found'], 404);
        }

        return $this->json($review, 200, [], ['groups' => 'review:read']);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $review = $this->reviewService->updateReview($id, $data);
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }

        if (!$review) {
            return $this->json(['error' => 'Review not found'], 404);
        }

        return $this->json($review, 200, [], ['groups' => 'review:read']);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $success = $this->reviewService->deleteReview($id);

        if (!$success) {
            return $this->json(['error' => 'Review not found'], 404);
        }

        return $this->json(['message' => 'Review deleted successfully']);
    }
}
