<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use App\Entity\Service;

#[ORM\Entity]
class Admin
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_admin;

    #[ORM\Column(type: "string", length: 255)]
    private string $speciality;

    #[ORM\Column(type: "text")]
    private string $experience;

        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "admins")]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id_user', onDelete: 'CASCADE')]
    private User $user_id;

    public function getId_admin()
    {
        return $this->id_admin;
    }

    public function setId_admin($value)
    {
        $this->id_admin = $value;
    }

    public function getSpeciality()
    {
        return $this->speciality;
    }

    public function setSpeciality($value)
    {
        $this->speciality = $value;
    }

    public function getExperience()
    {
        return $this->experience;
    }

    public function setExperience($value)
    {
        $this->experience = $value;
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($value)
    {
        $this->user_id = $value;
    }

    #[ORM\OneToMany(mappedBy: "id_prestataire", targetEntity: Equipement::class)]
    private Collection $equipements;

        public function getEquipements(): Collection
        {
            return $this->equipements;
        }
    
        public function addEquipement(Equipement $equipement): self
        {
            if (!$this->equipements->contains($equipement)) {
                $this->equipements[] = $equipement;
                $equipement->setId_prestataire($this);
            }
    
            return $this;
        }
    
        public function removeEquipement(Equipement $equipement): self
        {
            if ($this->equipements->removeElement($equipement)) {
                // set the owning side to null (unless already changed)
                if ($equipement->getId_prestataire() === $this) {
                    $equipement->setId_prestataire(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "PrestataireID", targetEntity: Service::class)]
    private Collection $services;

        public function getServices(): Collection
        {
            return $this->services;
        }
    
        public function addService(Service $service): self
        {
            if (!$this->services->contains($service)) {
                $this->services[] = $service;
                $service->setPrestataireID($this);
            }
    
            return $this;
        }
    
        public function removeService(Service $service): self
        {
            if ($this->services->removeElement($service)) {
                // set the owning side to null (unless already changed)
                if ($service->getPrestataireID() === $this) {
                    $service->setPrestataireID(null);
                }
            }
    
            return $this;
        }
}
