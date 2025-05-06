<?php

namespace App\Entity;

use App\Repository\PlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlanRepository::class)]
class Plan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]

    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le jour ne peut pas être vide.")]
    private ?string $Jour = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La nutrition ne peut pas être vide.")]
    private ?string $Nutration = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Les muscles ne peuvent pas être vides.")]
    private ?string $Muscle = null;

    #[ORM\Column(type: "float")]
    #[Assert\NotNull(message: "La distance doit être renseignée.")]
    #[Assert\PositiveOrZero(message: "La distance doit être un nombre positif.")]
    private ?float $Course = null;
    /**
     * @var Collection<int, Objectif>
     */
    #[ORM\OneToMany(targetEntity: Objectif::class, mappedBy: 'plan')]
    private Collection $Plan;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Objectif $id_obj = null;

    public function __construct()
    {
        $this->Plan = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJour(): ?string
    {
        return $this->Jour;
    }

    public function setJour(string $Jour): static
    {
        $this->Jour = $Jour;

        return $this;
    }

    public function getNutration(): ?string
    {
        return $this->Nutration;
    }

    public function setNutration(string $Nutration): static
    {
        $this->Nutration = $Nutration;

        return $this;
    }

    public function getMuscle(): ?string
    {
        return $this->Muscle;
    }

    public function setMuscle(string $Muscle): static
    {
        $this->Muscle = $Muscle;

        return $this;
    }

    public function getCourse(): ?string
    {
        return $this->Course;
    }

    public function setCourse(string $Course): static
    {
        $this->Course = $Course;

        return $this;
    }

    /**
     * @return Collection<int, Objectif>
     */
    public function getPlan(): Collection
    {
        return $this->Plan;
    }

    public function addPlan(Objectif $plan): static
    {
        if (!$this->Plan->contains($plan)) {
            $this->Plan->add($plan);
            $plan->setPlan($this);
        }

        return $this;
    }

    public function removePlan(Objectif $plan): static
    {
        if ($this->Plan->removeElement($plan)) {
            // set the owning side to null (unless already changed)
            if ($plan->getPlan() === $this) {
                $plan->setPlan(null);
            }
        }

        return $this;
    }

    public function getIdObj(): ?Objectif
    {
        return $this->id_obj;
    }

    public function setIdObj(?Objectif $id_obj): static
    {
        $this->id_obj = $id_obj;

        return $this;
    }
}
