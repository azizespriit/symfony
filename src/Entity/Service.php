<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Admin;
use Doctrine\Common\Collections\Collection;
use App\Entity\Reclamation;

#[ORM\Entity]
class Service
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $ID;

    #[ORM\Column(type: "string", length: 20)]
    private string $Title;

    #[ORM\Column(type: "string", length: 255)]
    private string $Description;

    #[ORM\Column(type: "float")]
    private float $Price;

    #[ORM\Column(type: "boolean")]
    private bool $Disponibility;

    #[ORM\Column(type: "string", length: 255)]
    private string $Type;

    #[ORM\Column(type: "integer")]
    private int $RateCount;

    #[ORM\Column(type: "float")]
    private float $RateSum;

        #[ORM\ManyToOne(targetEntity: Admin::class, inversedBy: "services")]
    #[ORM\JoinColumn(name: 'PrestataireID', referencedColumnName: 'id_admin', onDelete: 'CASCADE')]
    private Admin $PrestataireID;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $CreatedAt;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $UpdatedAt;

    public function getID()
    {
        return $this->ID;
    }

    public function setID($value)
    {
        $this->ID = $value;
    }

    public function getTitle()
    {
        return $this->Title;
    }

    public function setTitle($value)
    {
        $this->Title = $value;
    }

    public function getDescription()
    {
        return $this->Description;
    }

    public function setDescription($value)
    {
        $this->Description = $value;
    }

    public function getPrice()
    {
        return $this->Price;
    }

    public function setPrice($value)
    {
        $this->Price = $value;
    }

    public function getDisponibility()
    {
        return $this->Disponibility;
    }

    public function setDisponibility($value)
    {
        $this->Disponibility = $value;
    }

    public function getType()
    {
        return $this->Type;
    }

    public function setType($value)
    {
        $this->Type = $value;
    }

    public function getRateCount()
    {
        return $this->RateCount;
    }

    public function setRateCount($value)
    {
        $this->RateCount = $value;
    }

    public function getRateSum()
    {
        return $this->RateSum;
    }

    public function setRateSum($value)
    {
        $this->RateSum = $value;
    }

    public function getPrestataireID()
    {
        return $this->PrestataireID;
    }

    public function setPrestataireID($value)
    {
        $this->PrestataireID = $value;
    }

    public function getCreatedAt()
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt($value)
    {
        $this->CreatedAt = $value;
    }

    public function getUpdatedAt()
    {
        return $this->UpdatedAt;
    }

    public function setUpdatedAt($value)
    {
        $this->UpdatedAt = $value;
    }

    #[ORM\OneToMany(mappedBy: "ServiceID", targetEntity: Avis::class)]
    private Collection $aviss;

        public function getAviss(): Collection
        {
            return $this->aviss;
        }
    
        public function addAvis(Avis $avis): self
        {
            if (!$this->aviss->contains($avis)) {
                $this->aviss[] = $avis;
                $avis->setServiceID($this);
            }
    
            return $this;
        }
    
        public function removeAvis(Avis $avis): self
        {
            if ($this->aviss->removeElement($avis)) {
                // set the owning side to null (unless already changed)
                if ($avis->getServiceID() === $this) {
                    $avis->setServiceID(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "id_service", targetEntity: Reclamation::class)]
    private Collection $reclamations;

        public function getReclamations(): Collection
        {
            return $this->reclamations;
        }
    
        public function addReclamation(Reclamation $reclamation): self
        {
            if (!$this->reclamations->contains($reclamation)) {
                $this->reclamations[] = $reclamation;
                $reclamation->setId_service($this);
            }
    
            return $this;
        }
    
        public function removeReclamation(Reclamation $reclamation): self
        {
            if ($this->reclamations->removeElement($reclamation)) {
                // set the owning side to null (unless already changed)
                if ($reclamation->getId_service() === $this) {
                    $reclamation->setId_service(null);
                }
            }
    
            return $this;
        }
}
