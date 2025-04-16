<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Jour
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "integer")]
    private int $id_plan;

    #[ORM\Column(type: "string", length: 20)]
    private string $Jour;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getId_plan()
    {
        return $this->id_plan;
    }

    public function setId_plan($value)
    {
        $this->id_plan = $value;
    }

    public function getJour()
    {
        return $this->Jour;
    }

    public function setJour($value)
    {
        $this->Jour = $value;
    }
}
