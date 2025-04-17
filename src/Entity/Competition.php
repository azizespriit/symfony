<?php

namespace App\Entity;

use App\Repository\CompetitionRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: CompetitionRepository::class)]
#[ORM\Table(name: "competitions")]
class Competition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "IdC", type: "integer")]
    private ?int $idC = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(name: "DateC", type: 'date')]
    private ?\DateTimeInterface $dateC = null;

    #[ORM\Column(name: "NbPart", type: "integer")]
    private ?int $nbPart = null;

    #[ORM\Column(name: "LieuC", length: 255)]
    private ?string $lieuC = null;

    #[ORM\OneToMany(mappedBy: "competition", targetEntity: Reservation::class, orphanRemoval: true)]
    private Collection $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getIdC(): ?int
    {
        return $this->idC;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getDateC(): ?\DateTimeInterface
    {
        return $this->dateC;
    }

    public function setDateC(\DateTimeInterface $dateC): self
    {
        $this->dateC = $dateC;
        return $this;
    }

    public function getNbPart(): ?int
    {
        return $this->nbPart;
    }

    public function setNbPart(int $nbPart): self
    {
        $this->nbPart = $nbPart;
        return $this;
    }

    public function getLieuC(): ?string
    {
        return $this->lieuC;
    }

    public function setLieuC(string $lieuC): self
    {
        $this->lieuC = $lieuC;
        return $this;
    }

    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setCompetition($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            if ($reservation->getCompetition() === $this) {
                $reservation->setCompetition(null);
            }
        }

        return $this;
    }
}
