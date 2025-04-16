<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Reclamation;

#[ORM\Entity]
class Produit
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255)]
    private string $nom;

    #[ORM\Column(type: "float")]
    private float $prix;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "integer")]
    private int $stock;

    #[ORM\Column(type: "string", length: 255)]
    private string $photo;

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

    public function getPrix()
    {
        return $this->prix;
    }

    public function setPrix($value)
    {
        $this->prix = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getStock()
    {
        return $this->stock;
    }

    public function setStock($value)
    {
        $this->stock = $value;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($value)
    {
        $this->photo = $value;
    }

    #[ORM\OneToMany(mappedBy: "id_produit", targetEntity: Panier_produit::class)]
    private Collection $panier_produits;

        public function getPanier_produits(): Collection
        {
            return $this->panier_produits;
        }
    
        public function addPanier_produit(Panier_produit $panier_produit): self
        {
            if (!$this->panier_produits->contains($panier_produit)) {
                $this->panier_produits[] = $panier_produit;
                $panier_produit->setId_produit($this);
            }
    
            return $this;
        }
    
        public function removePanier_produit(Panier_produit $panier_produit): self
        {
            if ($this->panier_produits->removeElement($panier_produit)) {
                // set the owning side to null (unless already changed)
                if ($panier_produit->getId_produit() === $this) {
                    $panier_produit->setId_produit(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "id_produit", targetEntity: Reclamation::class)]
    private Collection $reclamations;
}
