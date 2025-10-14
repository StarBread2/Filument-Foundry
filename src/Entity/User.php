<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use App\Entity\UserOrder; # UserOrder Relationship

use Symfony\Component\Validator\Constraints as Assert; # Sanitization

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: "Username cannot be empty")]
    #[Assert\Length(min: 3, max: 20, minMessage: "Too short", maxMessage: "Too long")]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 6, max: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Full name cannot be empty")]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $fullName = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $address = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    /**
     * @var Collection<int, UserOrder>
     *
     * One User can have many UserOrders.
     * The "mappedBy" must match the property name in UserOrder (which is "$user").
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserOrder::class, orphanRemoval: true)]
    private Collection $userOrders;

    #[ORM\Column]
    private ?bool $isAdmin = null;

    public function __construct()
    {
        // Always initialize Doctrine collections
        $this->userOrders = new ArrayCollection();
    }

    // ───────────────────────────────
    // GETTERS AND SETTERS
    // ───────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    // ───────────────────────────────
    // RELATIONSHIP METHODS
    // ───────────────────────────────

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
            $userOrder->setUser($this);
        }

        return $this;
    }

    public function removeUserOrder(UserOrder $userOrder): static
    {
        if ($this->userOrders->removeElement($userOrder)) {
            // If this order still points to this user, clear it
            if ($userOrder->getUser() === $this) {
                $userOrder->setUser(null);
            }
        }

        return $this;
    }

    public function isAdmin(): ?bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): static
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }
}
