<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Competition;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: "reservations")]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "IdR", type: "integer")]
    private ?int $idR = null;

    #[ORM\Column(name: "NomP", type: "string", length: 20)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide.")]
    #[Assert\Length(max: 20, maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $nomP = null;

    #[ORM\Column(name: "PrenomP", type: "string", length: 20)]
    #[Assert\NotBlank(message: "Le prénom ne peut pas être vide.")]
    #[Assert\Length(max: 20, maxMessage: "Le prénom ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $prenomP = null;

    #[ORM\Column(name: "Email", type: "string", length: 255)]
    #[Assert\NotBlank(message: "L'email ne peut pas être vide.")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide.")]
    private ?string $email = null;

    #[ORM\Column(name: "Num", type: "integer")]
    #[Assert\NotBlank(message: "Le numéro ne peut pas être vide.")]
    #[Assert\GreaterThan(value: 0, message: "Le numéro doit être supérieur à zéro.")]
    private ?int $num = null;

    #[ORM\Column(name: "DateR", type: "date")]
    private ?\DateTimeInterface $dateR = null;

    #[ORM\Column(name: "ModeP", type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le mode de paiement ne peut pas être vide.")]
    #[Assert\Length(max: 255, maxMessage: "Le mode de paiement ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $modeP = null;

    #[ORM\ManyToOne(targetEntity: Competition::class, inversedBy: "reservations")]
    #[ORM\JoinColumn(name: "IdC", referencedColumnName: "IdC", nullable: false)]
    private ?Competition $competition = null;

    public function getIdR(): ?int
    {
        return $this->idR;
    }

    public function getNomP(): ?string
    {
        return $this->nomP;
    }

    public function setNomP(string $nomP): self
    {
        $this->nomP = $nomP;
        return $this;
    }

    public function getPrenomP(): ?string
    {
        return $this->prenomP;
    }

    public function setPrenomP(string $prenomP): self
    {
        $this->prenomP = $prenomP;
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

    public function getNum(): ?int
    {
        return $this->num;
    }

    public function setNum(int $num): self
    {
        $this->num = $num;
        return $this;
    }

    public function getDateR(): ?\DateTimeInterface
    {
        return $this->dateR;
    }

    public function setDateR(\DateTimeInterface $dateR): self
    {
        $this->dateR = $dateR;
        return $this;
    }

    public function getModeP(): ?string
    {
        return $this->modeP;
    }

    public function setModeP(string $modeP): self
    {
        $this->modeP = $modeP;
        return $this;
    }

    public function getCompetition(): ?Competition
    {
        return $this->competition;
    }

    public function setCompetition(?Competition $competition): self
    {
        $this->competition = $competition;
        return $this;
    }
}
