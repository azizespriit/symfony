<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du produit est obligatoire')]
    #[Assert\Length(
        min: 2, 
        max: 255, 
        minMessage: 'Le nom du produit doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom du produit ne peut pas dépasser {{ limit }} caractères'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9\séèêëàâäôöùûüç\-_.,&\']+$/',
        message: 'Le nom du produit contient des caractères non autorisés'
    )]
    private ?string $nom = null;

    #[ORM\Column(type: 'float')]
    #[Assert\NotBlank(message: 'Le prix du produit est obligatoire')]
    #[Assert\Positive(message: 'Le prix doit être supérieur à zéro')]
    #[Assert\Type(
        type: 'float',
        message: 'Le prix {{ value }} n\'est pas un nombre valide.'
    )]
    #[Assert\LessThanOrEqual(
        value: 99999.99,
        message: 'Le prix ne peut pas dépasser 99999.99 €'
    )]
    private ?float $prix = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(
        max: 2000,
        maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le stock est obligatoire')]
    #[Assert\GreaterThanOrEqual(
        value: 0,
        message: 'Le stock ne peut pas être négatif'
    )]
    #[Assert\Type(
        type: 'integer',
        message: 'Le stock {{ value }} n\'est pas un nombre entier valide.'
    )]
    #[Assert\LessThanOrEqual(
        value: 9999,
        message: 'Le stock ne peut pas dépasser 9999 unités'
    )]
    private ?int $stock = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    /**
     * @var File|null
     */
    #[Assert\Image(
        maxSize: '5M', 
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        mimeTypesMessage: 'Veuillez télécharger une image valide (JPG, PNG, WEBP, GIF)',
        maxSizeMessage: 'L\'image ne doit pas dépasser {{ limit }}',
        maxWidth: 4000,
        maxHeight: 4000,
        maxWidthMessage: 'La largeur de l\'image ne doit pas dépasser {{ max_width }} pixels',
        maxHeightMessage: 'La hauteur de l\'image ne doit pas dépasser {{ max_height }} pixels'
    )]
    #[ORM\Transient]
    private $photoFile;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: PanierProduit::class)]
    private Collection $panierProduits;

    public function __construct()
    {
        $this->panierProduits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getPhotoFile(): ?File
    {
        return $this->photoFile;
    }

    public function setPhotoFile(?File $photoFile): self
    {
        $this->photoFile = $photoFile;

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
            $panierProduit->setProduit($this);
        }

        return $this;
    }

    public function removePanierProduit(PanierProduit $panierProduit): self
    {
        if ($this->panierProduits->removeElement($panierProduit)) {
            // set the owning side to null (unless already changed)
            if ($panierProduit->getProduit() === $this) {
                $panierProduit->setProduit(null);
            }
        }

        return $this;
    }
} 