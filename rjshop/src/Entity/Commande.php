<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $id_panier = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire')]
    #[Assert\Email(
        message: 'L\'email {{ value }} n\'est pas un email valide',
    )]
    #[Assert\Length(
        max: 255,
        maxMessage: 'L\'email ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $email = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: 'La date de commande ne peut pas être vide')]
    #[Assert\Type("\DateTimeInterface", message: 'La date n\'est pas valide')]
    private ?\DateTimeInterface $date_commande = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'adresse de livraison est obligatoire')]
    #[Assert\Length(
        min: 10,
        max: 255,
        minMessage: 'L\'adresse doit comporter au moins {{ limit }} caractères',
        maxMessage: 'L\'adresse ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $localisation = null;

    #[ORM\OneToOne(inversedBy: 'commande', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'id_panier', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull(message: 'Le panier associé est obligatoire')]
    private ?Panier $panier = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdPanier(): ?int
    {
        return $this->id_panier;
    }

    public function setIdPanier(int $id_panier): self
    {
        $this->id_panier = $id_panier;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getDateCommande(): ?\DateTimeInterface
    {
        return $this->date_commande;
    }

    public function getDate_commande(): ?\DateTimeInterface
    {
        return $this->date_commande;
    }

    public function setDateCommande(\DateTimeInterface $date_commande): self
    {
        $this->date_commande = $date_commande;

        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): self
    {
        $this->localisation = $localisation;

        return $this;
    }

    public function getPanier(): ?Panier
    {
        return $this->panier;
    }

    public function setPanier(Panier $panier): self
    {
        $this->panier = $panier;

        return $this;
    }
} 