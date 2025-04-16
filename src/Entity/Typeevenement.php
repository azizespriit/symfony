<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Typeevenement
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_typeEvenement;

    #[ORM\Column(type: "string", length: 255)]
    private string $label;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $updateAt;

    public function getId_typeEvenement()
    {
        return $this->id_typeEvenement;
    }

    public function setId_typeEvenement($value)
    {
        $this->id_typeEvenement = $value;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($value)
    {
        $this->label = $value;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($value)
    {
        $this->createdAt = $value;
    }

    public function getUpdateAt()
    {
        return $this->updateAt;
    }

    public function setUpdateAt($value)
    {
        $this->updateAt = $value;
    }
}
