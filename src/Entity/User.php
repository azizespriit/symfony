<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use App\Entity\Client;
use App\Entity\Admin;

#[ORM\Entity]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_user", type: "integer")]
    private int $id_user;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le CIN est obligatoire.")]
    #[Assert\Length(min: 8, max: 8, exactMessage: "Le CIN doit contenir exactement 8 chiffres.")]
    #[Assert\Regex(pattern: "/^\d{8}$/", message: "Le CIN doit contenir uniquement des chiffres.")]
    private string $cin;

    #[ORM\Column(name:'lastname', type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le nom est requis.")]
    #[Assert\Length(min: 2, max: 50)]
    private string $lastName;

    #[ORM\Column(name:'firstname', type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le prénom est requis.")]
    #[Assert\Length(min: 2, max: 50)]
    private string $firstName;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\Choice(choices: ["Male", "Female"], message: "Veuillez choisir un genre valide.")]
    private string $gender;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le numéro de téléphone est requis.")]
    #[Assert\Regex(pattern: "/^\d{8}$/", message: "Le numéro doit contenir exactement 8 chiffres.")]
    private string $phone;

    #[ORM\Column(type: "string", length: 255)]
    private string $roles = 'ROLE_USER';

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "L'adresse email est requise.")]
    #[Assert\Email(message: "L'adresse email n'est pas valide.")]
    private string $email;

    #[ORM\Column(type: "string", length: 255)]
    private string $password;

    #[ORM\Column(name:'isLocked', type: "boolean", nullable: true)]
    private bool $isLocked = false;

    #[ORM\Column(name: 'failedAttempts', type: 'integer', nullable: true)]
    private ?int $failedAttempts = null;

    #[ORM\Column(name:'lockoutTime', type: "datetime", nullable: true)]
    private ?\DateTimeInterface $lockoutTime = null;

    #[ORM\Column(name: 'createdAt', type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updateAt', type: 'datetime')]
    private ?\DateTimeInterface $updateAt = null;

    #[ORM\Column(name:'reset_token', type: "string", length: 255)]
    private string $reset_token;

    #[ORM\Column(name:'token_expiry', type: "datetime")]
    private \DateTimeInterface $token_expiry;

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: Admin::class)]
    private Collection $admins;

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: Client::class)]
    private Collection $clients;

    public function __construct()
    {
        $this->admins = new ArrayCollection();
        $this->clients = new ArrayCollection();
        $this->isLocked = false;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updateAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updateAt = new \DateTimeImmutable();
    }

    public function getId_user(): int
    {
        return $this->id_user;
    }

//    public function setId_user(int $value): void
//    {
//        $this->id_user = $value;
//    }

    public function getCin(): string
    {
        return $this->cin;
    }

    public function setCin(string $value): void
    {
        $this->cin = $value;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $value): void
    {
        $this->lastName = $value;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $value): void
    {
        $this->firstName = $value;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function setGender(string $value): void
    {
        $this->gender = $value;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $value): void
    {
        $this->phone = $value;
    }

    public function getRoles(): array
    {
        // Handle both comma-separated and single role strings
        if (str_contains($this->roles, ',')) {
            return array_map('trim', explode(',', $this->roles));
        }
        return [$this->roles]; // Always return array
    }

// For setting roles - accepts both string and array
    public function setRoles(string|array $roles): void
    {
        if (is_array($roles)) {
            $this->roles = implode(',', $roles);
        } else {
            $this->roles = $roles;
        }
    }

// For display purposes
    public function getRolesAsString(): string
    {
        return $this->roles; // Return the raw string
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $value): void
    {
        $this->email = $value;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $value): void
    {
        $this->password = $value;
    }

    // Getter for the isLocked property
    public function getIsLocked(): bool
    {
        return $this->isLocked;
    }

    // Setter for the isLocked property
    public function setIsLocked(bool $isLocked): self
    {
        $this->isLocked = $isLocked;
        return $this;
    }

    // Implement the method to check if the account is locked
    public function isAccountNonLocked(): bool
    {
        return !$this->isLocked; // Returns true if the account is NOT locked
    }
    public function getFailedAttempts(): ?int
    {
        return $this->failedAttempts;
    }

    public function setFailedAttempts(?int $value): void
    {
        $this->failedAttempts = $value;
    }

    public function getLockoutTime(): ?\DateTimeInterface
    {
        return $this->lockoutTime;
    }

    public function setLockoutTime(?\DateTimeInterface $value): void
    {
        $this->lockoutTime = $value;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $value): void
    {
        $this->createdAt = $value;
    }

    public function getUpdateAt(): \DateTimeInterface
    {
        return $this->updateAt;
    }

    public function setUpdateAt(\DateTimeInterface $value): void
    {
        $this->updateAt = $value;
    }

    public function getReset_token(): string
    {
        return $this->reset_token;
    }

    public function setReset_token(string $value): void
    {
        $this->reset_token = $value;
    }

    public function getToken_expiry(): \DateTimeInterface
    {
        return $this->token_expiry;
    }

    public function setToken_expiry(\DateTimeInterface $value): void
    {
        $this->token_expiry = $value;
    }

    public function getAdmins(): Collection
    {
        return $this->admins;
    }

    public function addAdmin(Admin $admin): self
    {
        if (!$this->admins->contains($admin)) {
            $this->admins[] = $admin;
            $admin->setUser_id($this);
        }

        return $this;
    }

    public function removeAdmin(Admin $admin): self
    {
        if ($this->admins->removeElement($admin)) {
            if ($admin->getUser_id() === $this) {
                $admin->setUser_id(null);
            }
        }

        return $this;
    }

    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): self
    {
        if (!$this->clients->contains($client)) {
            $this->clients[] = $client;
            $client->setUser_id($this);
        }

        return $this;
    }

    public function removeClient(Client $client): self
    {
        if ($this->clients->removeElement($client)) {
            if ($client->getUser_id() === $this) {
                $client->setUser_id(null);
            }
        }

        return $this;
    }

    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        return $this->email; // or whatever field you use for login
    }

    // PasswordAuthenticatedUserInterface method

}
