<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use App\Entity\UserOrder;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity; # UserOrder Relationship
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\Validator\Constraints as Assert; # Sanitization

// FOR HASHING
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
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

    #[ORM\Column(type: "integer")]
    private int $orderCount = 0;

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

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    public function __construct()
    {
        // Always initialize Doctrine collections
        $this->userOrders = new ArrayCollection();

        // default roles
        $this->roles = ['ROLE_USER'];

        $this->createdAt = new \DateTime(); 
    }

    // ───────────────────────────────
    // GETTERS AND SETTERS
    // ───────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
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

    // NIGGAS
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles());
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary sensitive data, clear it here
    }

    public function getOrderCount(): int
    {
        return $this->orderCount;
    }

    public function setOrderCount(int $orderCount): static
    {
        $this->orderCount = $orderCount;
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
}


/*
table name: user_order
id (int)
material_id (int) index -> material.id
finish_id (int) index -> finish.id
color_id (int) index -> color.id
user_id (int) index -> user.id
file_path (varchar)
order_state (varchar)
delivery_date (date)
delivery_arrival (date)
price_total (decimal(10,2))
created_at (datetime)
quantity (int)
model_multiplier (double)

table name: user
id (int)
password (varchar)
full_name (varchar)
address (longtext)
email  (varchar)
created_at (datetime)
roles (json)
order_count (int)

table name: material
id (int)
name (varchar)
details (longtext)
price (double)
properties (longtext)
availability (tinyint(1))
image_path (varchar)

table name: finish
id (int)
name (varchar)
details (longtext)
price (double)
properties (longtext)
availability (tinyint)
image_path (varchar)

table name: color
id (int)
name (varchar)
price (double)
image_path (varchar)
availability (tinyint)
color_hex (varchar(7))









 */