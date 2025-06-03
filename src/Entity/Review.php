<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $textReview = null;

    #[ORM\Column]
    private ?int $numberTone = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?bool $isModerated = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    private ?Product $productId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTextReview(): ?string
    {
        return $this->textReview;
    }

    public function setTextReview(string $textReview): static
    {
        $this->textReview = $textReview;

        return $this;
    }

    public function getNumberTone(): ?int
    {
        return $this->numberTone;
    }

    public function setNumberTone(int $numberTone): static
    {
        $this->numberTone = $numberTone;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isModerated(): ?bool
    {
        return $this->isModerated;
    }

    public function setIsModerated(bool $isModerated): static
    {
        $this->isModerated = $isModerated;

        return $this;
    }

    public function getProductId(): ?Product
    {
        return $this->productId;
    }

    public function setProductId(?Product $productId): static
    {
        $this->productId = $productId;

        return $this;
    }
}
