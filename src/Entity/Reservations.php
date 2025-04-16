<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Reservations
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $IdR;

    #[ORM\Column(type: "string", length: 255)]
    private string $NomP;

    #[ORM\Column(type: "string", length: 255)]
    private string $PrenomP;

    #[ORM\Column(type: "string", length: 255)]
    private string $Email;

    #[ORM\Column(type: "integer")]
    private int $Num;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $DateR;

    #[ORM\Column(type: "string", length: 255)]
    private string $ModeP;

    #[ORM\Column(type: "integer")]
    private int $IdC;

    public function getIdR()
    {
        return $this->IdR;
    }

    public function setIdR($value)
    {
        $this->IdR = $value;
    }

    public function getNomP()
    {
        return $this->NomP;
    }

    public function setNomP($value)
    {
        $this->NomP = $value;
    }

    public function getPrenomP()
    {
        return $this->PrenomP;
    }

    public function setPrenomP($value)
    {
        $this->PrenomP = $value;
    }

    public function getEmail()
    {
        return $this->Email;
    }

    public function setEmail($value)
    {
        $this->Email = $value;
    }

    public function getNum()
    {
        return $this->Num;
    }

    public function setNum($value)
    {
        $this->Num = $value;
    }

    public function getDateR()
    {
        return $this->DateR;
    }

    public function setDateR($value)
    {
        $this->DateR = $value;
    }

    public function getModeP()
    {
        return $this->ModeP;
    }

    public function setModeP($value)
    {
        $this->ModeP = $value;
    }

    public function getIdC()
    {
        return $this->IdC;
    }

    public function setIdC($value)
    {
        $this->IdC = $value;
    }
}
