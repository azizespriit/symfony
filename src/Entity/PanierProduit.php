<?php

namespace App\Entity;

use App\Repository\PanierProduitRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PanierProduitRepository::class)]
class PanierProduit
{
    #[ORM\Id]
    #[ORM\Column]
    private ?int $id_panier = null;

    #[ORM\Id]
    #[ORM\Column]
    private ?int $id_produit = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La quantité est obligatoire')]
    #[Assert\Positive(message: 'La quantité doit être supérieure à zéro')]
    #[Assert\Type(
        type: 'integer',
        message: 'La quantité {{ value }} n\'est pas un nombre entier valide.'
    )]
    private ?int $quantite = null;

    #[ORM\ManyToOne(inversedBy: 'panierProduits')]
    #[ORM\JoinColumn(name: 'id_panier', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull(message: 'Le panier associé est obligatoire')]
    private ?Panier $panier = null;

    #[ORM\ManyToOne(inversedBy: 'panierProduits')]
    #[ORM\JoinColumn(name: 'id_produit', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull(message: 'Le produit associé est obligatoire')]
    private ?Produit $produit = null;

    public function getIdPanier(): ?int
    {
        return $this->id_panier;
    }

    public function setIdPanier(int $id_panier): self
    {
        $this->id_panier = $id_panier;

        return $this;
    }

    public function getIdProduit(): ?int
    {
        return $this->id_produit;
    }

    public function setIdProduit(int $id_produit): self
    {
        $this->id_produit = $id_produit;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getPanier(): ?Panier
    {
        return $this->panier;
    }

    public function setPanier(?Panier $panier): self
    {
        $this->panier = $panier;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;

        return $this;
    }
} 