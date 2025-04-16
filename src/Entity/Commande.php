<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Panier;

#[ORM\Entity]
class Commande
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

        #[ORM\ManyToOne(targetEntity: Panier::class, inversedBy: "commandes")]
    #[ORM\JoinColumn(name: 'id_panier', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Panier $id_panier;

    #[ORM\Column(type: "string", length: 255)]
    private string $email;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_commande;

    #[ORM\Column(type: "string", length: 255)]
    private string $localisation;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getId_panier()
    {
        return $this->id_panier;
    }

    public function setId_panier($value)
    {
        $this->id_panier = $value;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($value)
    {
        $this->email = $value;
    }

    public function getDate_commande()
    {
        return $this->date_commande;
    }

    public function setDate_commande($value)
    {
        $this->date_commande = $value;
    }

    public function getLocalisation()
    {
        return $this->localisation;
    }

    public function setLocalisation($value)
    {
        $this->localisation = $value;
    }
}
