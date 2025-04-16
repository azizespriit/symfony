<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Plan;

#[ORM\Entity]
class Objectif
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 20)]
    private string $nom;

    #[ORM\Column(type: "string", length: 255)]
    private string $image;

    #[ORM\Column(type: "string", length: 200)]
    private string $description;

    #[ORM\Column(type: "integer")]
    private int $niveau;

    #[ORM\Column(type: "integer")]
    private int $semaine;

    #[ORM\Column(type: "string", length: 255)]
    private string $lien;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($value)
    {
        $this->nom = $value;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($value)
    {
        $this->image = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getNiveau()
    {
        return $this->niveau;
    }

    public function setNiveau($value)
    {
        $this->niveau = $value;
    }

    public function getSemaine()
    {
        return $this->semaine;
    }

    public function setSemaine($value)
    {
        $this->semaine = $value;
    }

    public function getLien()
    {
        return $this->lien;
    }

    public function setLien($value)
    {
        $this->lien = $value;
    }

    #[ORM\OneToMany(mappedBy: "id_obj", targetEntity: Plan::class)]
    private Collection $plans;

        public function getPlans(): Collection
        {
            return $this->plans;
        }
    
        public function addPlan(Plan $plan): self
        {
            if (!$this->plans->contains($plan)) {
                $this->plans[] = $plan;
                $plan->setId_obj($this);
            }
    
            return $this;
        }
    
        public function removePlan(Plan $plan): self
        {
            if ($this->plans->removeElement($plan)) {
                // set the owning side to null (unless already changed)
                if ($plan->getId_obj() === $this) {
                    $plan->setId_obj(null);
                }
            }
    
            return $this;
        }
}
