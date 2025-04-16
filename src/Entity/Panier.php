<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Commande;

#[ORM\Entity]
class Panier
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "float")]
    private float $prix_total;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getPrix_total()
    {
        return $this->prix_total;
    }

    public function setPrix_total($value)
    {
        $this->prix_total = $value;
    }

    #[ORM\OneToMany(mappedBy: "id_panier", targetEntity: Panier_produit::class)]
    private Collection $panier_produits;

        public function getPanier_produits(): Collection
        {
            return $this->panier_produits;
        }
    
        public function addPanier_produit(Panier_produit $panier_produit): self
        {
            if (!$this->panier_produits->contains($panier_produit)) {
                $this->panier_produits[] = $panier_produit;
                $panier_produit->setId_panier($this);
            }
    
            return $this;
        }
    
        public function removePanier_produit(Panier_produit $panier_produit): self
        {
            if ($this->panier_produits->removeElement($panier_produit)) {
                // set the owning side to null (unless already changed)
                if ($panier_produit->getId_panier() === $this) {
                    $panier_produit->setId_panier(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "id_panier", targetEntity: Commande::class)]
    private Collection $commandes;
}
