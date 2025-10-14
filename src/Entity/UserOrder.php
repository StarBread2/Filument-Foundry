<?php

namespace App\Entity;

use App\Repository\UserOrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

#[ORM\Entity(repositoryClass: UserOrderRepository::class)]
class UserOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    #[ORM\ManyToOne(inversedBy: 'userOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Material $material = null;

    #[ORM\ManyToOne(inversedBy: 'userOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Finish $finish = null;

    #[ORM\ManyToOne(inversedBy: 'userOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Color $color = null;

    #[ORM\ManyToOne(inversedBy: 'userOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $file_path = null;

    public const STATE_PROCESSING = 'processing';
    public const STATE_PRINTING = 'printing';
    public const STATE_SHIPPED = 'shipped';
    public const STATE_DELIVERED = 'delivered';
    public const STATE_CANCELLED = 'cancelled';

    #[ORM\Column(length: 255)]
    private ?string $order_state = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $delievery_date = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $delivery_arrival = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price_total = null;

    #[ORM\Column]
    private ?\DateTime $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getMaterial(): ?Material
    {
        return $this->material;
    }

    public function setMaterial(?Material $material): static
    {
        $this->material = $material;

        return $this;
    }

    public function getFinish(): ?Finish
    {
        return $this->finish;
    }

    public function setFinish(?Finish $finish): static
    {
        $this->finish = $finish;

        return $this;
    }

    public function getColor(): ?Color
    {
        return $this->color;
    }

    public function setColor(?Color $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }


    public function getFilePath(): ?string
    {
        return $this->file_path;
    }

    public function setFilePath(string $file_path): static
    {
        $this->file_path = $file_path;

        return $this;
    }

    public function getOrderState(): ?string
    {
        return $this->order_state;
    }

    public function setOrderState(string $order_state): static
    {
        $this->order_state = $order_state;

        return $this;
    }

    public function getDelieveryDate(): ?\DateTime
    {
        return $this->delievery_date;
    }

    public function setDelieveryDate(\DateTime $delievery_date): static
    {
        $this->delievery_date = $delievery_date;

        return $this;
    }

    public function getDeliveryArrival(): ?\DateTime
    {
        return $this->delivery_arrival;
    }

    public function setDeliveryArrival(\DateTime $delivery_arrival): static
    {
        $this->delivery_arrival = $delivery_arrival;

        return $this;
    }

    public function getPriceTotal(): ?string
    {
        return $this->price_total;
    }

    public function setPriceTotal(string $price_total): static
    {
        $this->price_total = $price_total;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTime $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}
