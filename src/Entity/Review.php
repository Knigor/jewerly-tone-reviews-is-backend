<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['review:read'])]
    private ?User $user = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $textReview = null;

    #[ORM\Column]
    private ?int $numberTone = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?bool $isModerated = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Product $productId = null;

    #[ORM\Column]
    private ?int $rating = null;

    #[Groups(['review:read'])]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[Groups(['review:read'])]
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    #[Groups(['review:read'])]
    public function getTextReview(): ?string
    {
        return $this->textReview;
    }

    public function setTextReview(string $textReview): static
    {
        $this->textReview = $textReview;

        return $this;
    }
    #[Groups(['review:read'])]
    public function getNumberTone(): ?int
    {
        return $this->numberTone;
    }

    public function setNumberTone(int $numberTone): static
    {
        $this->numberTone = $numberTone;

        return $this;
    }
    #[Groups(['review:read'])]
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
    #[Groups(['review:read'])]
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
    #[Groups(['review:read'])]
    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): static
    {
        $this->rating = $rating;

        return $this;
    }
}
