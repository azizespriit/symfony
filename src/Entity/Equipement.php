<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Admin;
use Doctrine\Common\Collections\Collection;
use App\Entity\Reclamation;

#[ORM\Entity]
class Equipement
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_equipement;

    #[ORM\Column(type: "string", length: 255)]
    private string $name;

    #[ORM\Column(type: "string", length: 255)]
    private string $description;

    #[ORM\Column(type: "integer")]
    private int $image;

    #[ORM\Column(type: "integer")]
    private int $price;

    #[ORM\Column(type: "boolean")]
    private bool $disponibility;

    #[ORM\Column(type: "integer")]
    private int $quantity;

        #[ORM\ManyToOne(targetEntity: Admin::class, inversedBy: "equipements")]
    #[ORM\JoinColumn(name: 'id_prestataire', referencedColumnName: 'id_admin', onDelete: 'CASCADE')]
    private Admin $id_prestataire;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $updatedAt;

    public function getId_equipement()
    {
        return $this->id_equipement;
    }

    public function setId_equipement($value)
    {
        $this->id_equipement = $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($value)
    {
        $this->name = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($value)
    {
        $this->image = $value;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($value)
    {
        $this->price = $value;
    }

    public function getDisponibility()
    {
        return $this->disponibility;
    }

    public function setDisponibility($value)
    {
        $this->disponibility = $value;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity($value)
    {
        $this->quantity = $value;
    }

    public function getId_prestataire()
    {
        return $this->id_prestataire;
    }

    public function setId_prestataire($value)
    {
        $this->id_prestataire = $value;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($value)
    {
        $this->createdAt = $value;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($value)
    {
        $this->updatedAt = $value;
    }

    #[ORM\OneToMany(mappedBy: "id_equipement", targetEntity: Reclamation::class)]
    private Collection $reclamations;

        public function getReclamations(): Collection
        {
            return $this->reclamations;
        }
    
        public function addReclamation(Reclamation $reclamation): self
        {
            if (!$this->reclamations->contains($reclamation)) {
                $this->reclamations[] = $reclamation;
                $reclamation->setId_equipement($this);
            }
    
            return $this;
        }
    
        public function removeReclamation(Reclamation $reclamation): self
        {
            if ($this->reclamations->removeElement($reclamation)) {
                // set the owning side to null (unless already changed)
                if ($reclamation->getId_equipement() === $this) {
                    $reclamation->setId_equipement(null);
                }
            }
    
            return $this;
        }
}
