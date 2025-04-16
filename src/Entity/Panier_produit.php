<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Produit;

#[ORM\Entity]
class Panier_produit
{

    #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: Panier::class, inversedBy: "panier_produits")]
    #[ORM\JoinColumn(name: 'id_panier', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Panier $id_panier;

    #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: Produit::class, inversedBy: "panier_produits")]
    #[ORM\JoinColumn(name: 'id_produit', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Produit $id_produit;

    #[ORM\Column(type: "integer")]
    private int $quantite;

    public function getId_panier()
    {
        return $this->id_panier;
    }

    public function setId_panier($value)
    {
        $this->id_panier = $value;
    }

    public function getId_produit()
    {
        return $this->id_produit;
    }

    public function setId_produit($value)
    {
        $this->id_produit = $value;
    }

    public function getQuantite()
    {
        return $this->quantite;
    }

    public function setQuantite($value)
    {
        $this->quantite = $value;
    }
}
