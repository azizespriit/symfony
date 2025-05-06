<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(type: 'float')]
    #[Assert\NotNull(message: 'Le prix total ne peut pas être null')]
    #[Assert\GreaterThanOrEqual(
        value: 0,
        message: 'Le prix total ne peut pas être négatif'
    )]
    #[Assert\Type(
        type: 'float',
        message: 'Le prix total {{ value }} n\'est pas un nombre valide.'
    )]
    private float $prix_total = 0.0;

    #[ORM\OneToOne(mappedBy: 'panier', cascade: ['persist', 'remove'])]
    private ?Commande $commande = null;

    #[ORM\OneToMany(mappedBy: 'panier', targetEntity: PanierProduit::class, orphanRemoval: true)]
    #[Assert\Valid]
    private Collection $panierProduits;

    public function __construct()
    {
        $this->panierProduits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrixTotal(): float
    {
        return $this->prix_total;
    }

    public function getPrix_total(): float
    {
        return $this->prix_total;
    }

    public function setPrixTotal(float $prix_total): self
    {
        $this->prix_total = $prix_total;

        return $this;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(Commande $commande): self
    {
        // set the owning side of the relation if necessary
        if ($commande->getPanier() !== $this) {
            $commande->setPanier($this);
        }

        $this->commande = $commande;

        return $this;
    }

    /**
     * @return Collection<int, PanierProduit>
     */
    public function getPanierProduits(): Collection
    {
        return $this->panierProduits;
    }

    public function addPanierProduit(PanierProduit $panierProduit): self
    {
        if (!$this->panierProduits->contains($panierProduit)) {
            $this->panierProduits->add($panierProduit);
            $panierProduit->setPanier($this);
        }

        return $this;
    }

    public function removePanierProduit(PanierProduit $panierProduit): self
    {
        if ($this->panierProduits->removeElement($panierProduit)) {
            // set the owning side to null (unless already changed)
            if ($panierProduit->getPanier() === $this) {
                $panierProduit->setPanier(null);
            }
        }

        return $this;
    }
} 