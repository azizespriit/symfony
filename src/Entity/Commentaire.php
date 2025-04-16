<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Publication;

#[ORM\Entity]
class Commentaire
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

        #[ORM\ManyToOne(targetEntity: Publication::class, inversedBy: "commentaires")]
    #[ORM\JoinColumn(name: 'Id_pub', referencedColumnName: 'Id_pub', onDelete: 'CASCADE')]
    private Publication $Id_pub;

    #[ORM\Column(type: "integer")]
    private int $id_user;

    #[ORM\Column(type: "string", length: 255)]
    private string $contenu;

    #[ORM\Column(type: "string", length: 255)]
    private string $datee;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getId_pub()
    {
        return $this->Id_pub;
    }

    public function setId_pub($value)
    {
        $this->Id_pub = $value;
    }

    public function getId_user()
    {
        return $this->id_user;
    }

    public function setId_user($value)
    {
        $this->id_user = $value;
    }

    public function getContenu()
    {
        return $this->contenu;
    }

    public function setContenu($value)
    {
        $this->contenu = $value;
    }

    public function getDatee()
    {
        return $this->datee;
    }

    public function setDatee($value)
    {
        $this->datee = $value;
    }
}
