<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use App\Entity\Category;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use App\Enum\ProductSize;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nameProduct = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $descriptionProduct = null;

    #[ORM\Column]
    private ?int $priceProduct = null;

    #[ORM\Column(length: 255)]
    private ?string $metal = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?User $userId = null;

    /**
     * @var Collection<int, Review>
     */

    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'productId')]
    private Collection $reviews;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Category $category = null;


    #[ORM\Column(type: Types::JSON)]
    private array $sizeProduct = [];

    #[ORM\Column(length: 255)]
    private ?string $imgUrlProduct = null;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
    }
    #[Groups(['product:read'])]
    public function getId(): ?int
    {
        return $this->id;
    }
    #[Groups(['product:read'])]
    public function getNameProduct(): ?string
    {
        return $this->nameProduct;
    }

    public function setNameProduct(string $nameProduct): static
    {
        $this->nameProduct = $nameProduct;

        return $this;
    }
    #[Groups(['product:read'])]
    public function getDescriptionProduct(): ?string
    {
        return $this->descriptionProduct;
    }

    public function setDescriptionProduct(string $descriptionProduct): static
    {
        $this->descriptionProduct = $descriptionProduct;

        return $this;
    }
    #[Groups(['product:read'])]
    public function getPriceProduct(): ?int
    {
        return $this->priceProduct;
    }

    public function setPriceProduct(int $priceProduct): static
    {
        $this->priceProduct = $priceProduct;

        return $this;
    }
    #[Groups(['product:read'])]
    public function getMetal(): ?string
    {
        return $this->metal;
    }

    public function setMetal(string $metal): static
    {
        $this->metal = $metal;

        return $this;
    }
    #[Groups(['product:read'])]
    public function getUserId(): ?User
    {
        return $this->userId;
    }



    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function setUserId(?User $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setProductId($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getProductId() === $this) {
                $review->setProductId(null);
            }
        }

        return $this;
    }



    #[Groups(['product:read'])]
    public function getSizeProduct(): ?array
    {
        return $this->sizeProduct;
    }

    public function setSizeProduct(?array $sizes): self
    {
        $this->sizeProduct = $sizes;
        return $this;
    }
    #[Groups(['product:read'])]
    public function getImgUrlProduct(): ?string
    {
        return $this->imgUrlProduct;
    }

    public function setImgUrlProduct(string $imgUrlProduct): static
    {
        $this->imgUrlProduct = $imgUrlProduct;

        return $this;
    }
}
