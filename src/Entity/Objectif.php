<?php

namespace App\Entity;

use App\Repository\ObjectifRepository;
use App\Rating\Entity\Rating;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ObjectifRepository::class)]
class Objectif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide.")]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z\s]+$/",
        message: "Le nom doit contenir uniquement des lettres et des espaces."
    )]
    private ?string $nom = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    private ?string $description = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\NotBlank(message: "Le niveau est requis.")]
    #[Assert\Type(
        type: 'integer',
        message: "Le niveau doit être un nombre entier."
    )]
    private ?int $niveau = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\NotBlank(message: "La semaine est requise.")]
    #[Assert\Type(
        type: 'integer',
        message: "La semaine doit être un nombre entier."
    )]
    private ?int $semaine = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Url(message: "Le lien doit être une URL valide.")]
    private ?string $lien = null;

    #[ORM\OneToMany(targetEntity: Rating::class, mappedBy: 'objectif')]
    private Collection $ratings;

    public function __construct(
        ?string $nom = null,
        ?string $image = null,
        ?string $description = null,
        ?int $niveau = null,
        ?int $semaine = null,
        ?string $lien = null
    ) {
        $this->nom = $nom;
        $this->image = $image;
        $this->description = $description;
        $this->niveau = $niveau;
        $this->semaine = $semaine;
        $this->lien = $lien;
        $this->ratings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getNiveau(): ?int
    {
        return $this->niveau;
    }

    public function setNiveau(?int $niveau): self
    {
        $this->niveau = $niveau;
        return $this;
    }

    public function getSemaine(): ?int
    {
        return $this->semaine;
    }

    public function setSemaine(?int $semaine): self
    {
        $this->semaine = $semaine;
        return $this;
    }

    public function getLien(): ?string
    {
        return $this->lien;
    }

    public function setLien(?string $lien): self
    {
        $this->lien = $lien;
        return $this;
    }

    /**
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): self
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings->add($rating);
            $rating->setObjectif($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getObjectif() === $this) {
                $rating->setObjectif(null);
            }
        }

        return $this;
    }

    public function getAverageRating(): float
    {
        if ($this->ratings->isEmpty()) {
            return 0;
        }

        $total = 0;
        foreach ($this->ratings as $rating) {
            $total += $rating->getScore();
        }

        return round($total / $this->ratings->count(), 1);
    }

    public function getRatingCount(): int
    {
        return $this->ratings->count();
    }
}