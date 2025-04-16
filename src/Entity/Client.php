<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use App\Entity\Reclamation;

#[ORM\Entity]
class Client
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_client;

    #[ORM\Column(type: "integer")]
    private int $yearsOfExperience;

    #[ORM\Column(type: "integer")]
    private int $numberOfEventsOrganized;

        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "clients")]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id_user', onDelete: 'CASCADE')]
    private User $user_id;

    public function getId_client()
    {
        return $this->id_client;
    }

    public function setId_client($value)
    {
        $this->id_client = $value;
    }

    public function getYearsOfExperience()
    {
        return $this->yearsOfExperience;
    }

    public function setYearsOfExperience($value)
    {
        $this->yearsOfExperience = $value;
    }

    public function getNumberOfEventsOrganized()
    {
        return $this->numberOfEventsOrganized;
    }

    public function setNumberOfEventsOrganized($value)
    {
        $this->numberOfEventsOrganized = $value;
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($value)
    {
        $this->user_id = $value;
    }

    #[ORM\OneToMany(mappedBy: "OrganisateurID", targetEntity: Avis::class)]
    private Collection $aviss;

        public function getAviss(): Collection
        {
            return $this->aviss;
        }
    
        public function addAvis(Avis $avis): self
        {
            if (!$this->aviss->contains($avis)) {
                $this->aviss[] = $avis;
                $avis->setOrganisateurID($this);
            }
    
            return $this;
        }
    
        public function removeAvis(Avis $avis): self
        {
            if ($this->aviss->removeElement($avis)) {
                // set the owning side to null (unless already changed)
                if ($avis->getOrganisateurID() === $this) {
                    $avis->setOrganisateurID(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "id_organisateur", targetEntity: Reclamation::class)]
    private Collection $reclamations;

        public function getReclamations(): Collection
        {
            return $this->reclamations;
        }
    
        public function addReclamation(Reclamation $reclamation): self
        {
            if (!$this->reclamations->contains($reclamation)) {
                $this->reclamations[] = $reclamation;
                $reclamation->setId_organisateur($this);
            }
    
            return $this;
        }
    
        public function removeReclamation(Reclamation $reclamation): self
        {
            if ($this->reclamations->removeElement($reclamation)) {
                // set the owning side to null (unless already changed)
                if ($reclamation->getId_organisateur() === $this) {
                    $reclamation->setId_organisateur(null);
                }
            }
    
            return $this;
        }
}
