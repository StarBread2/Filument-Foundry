<?php

namespace App\Entity;

use App\Repository\UserOrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

enum OrderState: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case PRINTING = 'printing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
}

#[ORM\Entity(repositoryClass: UserOrderRepository::class)]
class UserOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

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

    #[ORM\Column(type: 'integer')]
    private int $quantity = 1;

    #[ORM\Column(length: 255)]
    private ?string $file_path = null;

    #[ORM\Column(enumType: OrderState::class)]
    private OrderState $order_state = OrderState::PENDING;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $delivery_date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $delivery_arrival = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $price_total = null;

    #[ORM\Column]
    private ?\DateTime $created_at = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $modelMultiplier = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $delivery_location = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOrderState(): OrderState
    {
        return $this->order_state;
    }

    public function setOrderState(OrderState $state): static
    {
        $this->order_state = $state;
        return $this;
    }

    public function getDeliveryDate(): ?\DateTime
    {
        return $this->delivery_date;
    }

    public function setDeliveryDate(\DateTime $delivery_date): static
    {
        $this->delivery_date = $delivery_date;

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

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getModelMultiplier(): ?float
    {
        return $this->modelMultiplier;
    }

    public function setModelMultiplier(?float $modelMultiplier): static
    {
        $this->modelMultiplier = $modelMultiplier;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    //GET THE PROJECT NAME BY LAST / AND REMOVE FILE EXTENSION
    public function getProjectName(): string
    {
        if (!$this->file_path) return 'Unknown';

        $file = basename($this->file_path); // test.obj
        return ucfirst(pathinfo($file, PATHINFO_FILENAME)); // Test
    }

    public function getDeliveryLocation(): ?string
    {
        return $this->delivery_location;
    }

    public function setDeliveryLocation(?string $delivery_location): static
    {
        $this->delivery_location = $delivery_location;
        return $this;
    }
}
