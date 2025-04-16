<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Produit;
use Doctrine\Common\Collections\Collection;
use App\Entity\Reponse;

#[ORM\Entity]
class Reclamation
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_reclamation;

    #[ORM\Column(type: "string", length: 255)]
    private string $object;

    #[ORM\Column(type: "string", length: 255)]
    private string $content;

    #[ORM\Column(type: "integer")]
    private int $ticket;

        #[ORM\ManyToOne(targetEntity: Service::class, inversedBy: "reclamations")]
    #[ORM\JoinColumn(name: 'id_service', referencedColumnName: 'ID', onDelete: 'CASCADE')]
    private Service $id_service;

        #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: "reclamations")]
    #[ORM\JoinColumn(name: 'id_organisateur', referencedColumnName: 'id_client', onDelete: 'CASCADE')]
    private Client $id_organisateur;

        #[ORM\ManyToOne(targetEntity: Equipement::class, inversedBy: "reclamations")]
    #[ORM\JoinColumn(name: 'id_equipement', referencedColumnName: 'id_equipement', onDelete: 'CASCADE')]
    private Equipement $id_equipement;

        #[ORM\ManyToOne(targetEntity: Produit::class, inversedBy: "reclamations")]
    #[ORM\JoinColumn(name: 'id_produit', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Produit $id_produit;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAT;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $updateAT;

    public function getId_reclamation()
    {
        return $this->id_reclamation;
    }

    public function setId_reclamation($value)
    {
        $this->id_reclamation = $value;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setObject($value)
    {
        $this->object = $value;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($value)
    {
        $this->content = $value;
    }

    public function getTicket()
    {
        return $this->ticket;
    }

    public function setTicket($value)
    {
        $this->ticket = $value;
    }

    public function getId_service()
    {
        return $this->id_service;
    }

    public function setId_service($value)
    {
        $this->id_service = $value;
    }

    public function getId_organisateur()
    {
        return $this->id_organisateur;
    }

    public function setId_organisateur($value)
    {
        $this->id_organisateur = $value;
    }

    public function getId_equipement()
    {
        return $this->id_equipement;
    }

    public function setId_equipement($value)
    {
        $this->id_equipement = $value;
    }

    public function getId_produit()
    {
        return $this->id_produit;
    }

    public function setId_produit($value)
    {
        $this->id_produit = $value;
    }

    public function getCreatedAT()
    {
        return $this->createdAT;
    }

    public function setCreatedAT($value)
    {
        $this->createdAT = $value;
    }

    public function getUpdateAT()
    {
        return $this->updateAT;
    }

    public function setUpdateAT($value)
    {
        $this->updateAT = $value;
    }

    #[ORM\OneToMany(mappedBy: "id_reclamation", targetEntity: Reponse::class)]
    private Collection $reponses;
}
