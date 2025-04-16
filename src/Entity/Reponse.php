<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Reclamation;

#[ORM\Entity]
class Reponse
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_reponse;

    #[ORM\Column(type: "string", length: 255)]
    private string $content;

        #[ORM\ManyToOne(targetEntity: Reclamation::class, inversedBy: "reponses")]
    #[ORM\JoinColumn(name: 'id_reclamation', referencedColumnName: 'id_reclamation', onDelete: 'CASCADE')]
    private Reclamation $id_reclamation;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $updateAT;

    public function getId_reponse()
    {
        return $this->id_reponse;
    }

    public function setId_reponse($value)
    {
        $this->id_reponse = $value;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($value)
    {
        $this->content = $value;
    }

    public function getId_reclamation()
    {
        return $this->id_reclamation;
    }

    public function setId_reclamation($value)
    {
        $this->id_reclamation = $value;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($value)
    {
        $this->createdAt = $value;
    }

    public function getUpdateAT()
    {
        return $this->updateAT;
    }

    public function setUpdateAT($value)
    {
        $this->updateAT = $value;
    }
}
