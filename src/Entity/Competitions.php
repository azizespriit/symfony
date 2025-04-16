<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Competitions
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $IdC;

    #[ORM\Column(type: "string", length: 255)]
    private string $Nom;

    #[ORM\Column(type: "string", length: 255)]
    private string $Description;

    #[ORM\Column(type: "string", length: 255)]
    private string $Type;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $DateC;

    #[ORM\Column(type: "integer")]
    private int $NbPart;

    #[ORM\Column(type: "string", length: 255)]
    private string $LieuC;

    public function getIdC()
    {
        return $this->IdC;
    }

    public function setIdC($value)
    {
        $this->IdC = $value;
    }

    public function getNom()
    {
        return $this->Nom;
    }

    public function setNom($value)
    {
        $this->Nom = $value;
    }

    public function getDescription()
    {
        return $this->Description;
    }

    public function setDescription($value)
    {
        $this->Description = $value;
    }

    public function getType()
    {
        return $this->Type;
    }

    public function setType($value)
    {
        $this->Type = $value;
    }

    public function getDateC()
    {
        return $this->DateC;
    }

    public function setDateC($value)
    {
        $this->DateC = $value;
    }

    public function getNbPart()
    {
        return $this->NbPart;
    }

    public function setNbPart($value)
    {
        $this->NbPart = $value;
    }

    public function getLieuC()
    {
        return $this->LieuC;
    }

    public function setLieuC($value)
    {
        $this->LieuC = $value;
    }
}
