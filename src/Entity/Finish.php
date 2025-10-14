<?php

namespace App\Entity;

use App\Repository\FinishRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FinishRepository::class)]
class Finish
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $details = null;

    #[ORM\Column]
    private ?float $price = null;

    /**
     * @var Collection<int, UserOrder>
     */
    #[ORM\OneToMany(targetEntity: UserOrder::class, mappedBy: 'finish')]
    private Collection $userOrders;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $properties = null;

    #[ORM\Column]
    private ?bool $availability = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image_path = null;

    public function __construct()
    {
        $this->userOrders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(string $details): static
    {
        $this->details = $details;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, UserOrder>
     */
    public function getUserOrders(): Collection
    {
        return $this->userOrders;
    }

    public function addUserOrder(UserOrder $userOrder): static
    {
        if (!$this->userOrders->contains($userOrder)) {
            $this->userOrders->add($userOrder);
            $userOrder->setFinish($this);
        }

        return $this;
    }

    public function removeUserOrder(UserOrder $userOrder): static
    {
        if ($this->userOrders->removeElement($userOrder)) {
            // set the owning side to null (unless already changed)
            if ($userOrder->getFinish() === $this) {
                $userOrder->setFinish(null);
            }
        }

        return $this;
    }

    public function getProperties(): ?string
    {
        return $this->properties;
    }

    public function setProperties(string $properties): static
    {
        $this->properties = $properties;

        return $this;
    }

    public function isAvailability(): ?bool
    {
        return $this->availability;
    }

    public function setAvailability(bool $availability): static
    {
        $this->availability = $availability;

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->image_path;
    }

    public function setImagePath(?string $image_path): static
    {
        $this->image_path = $image_path;

        return $this;
    }
}
