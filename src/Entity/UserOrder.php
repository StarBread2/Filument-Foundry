<?php

namespace App\Entity;

use App\Repository\UserOrderRepository;
use Doctrine\ORM\Mapping as ORM;

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
}
