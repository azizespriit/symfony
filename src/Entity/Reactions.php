<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Utilisateur;

#[ORM\Entity]
class Reactions
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

        #[ORM\ManyToOne(targetEntity: Publication::class, inversedBy: "reactionss")]
    #[ORM\JoinColumn(name: 'Id_pub', referencedColumnName: 'Id_pub', onDelete: 'CASCADE')]
    private Publication $Id_pub;

        #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "reactionss")]
    #[ORM\JoinColumn(name: 'Id_user', referencedColumnName: 'Id_user', onDelete: 'CASCADE')]
    private Utilisateur $Id_user;

    #[ORM\Column(type: "string", length: 255)]
    private string $type;

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
        return $this->Id_user;
    }

    public function setId_user($value)
    {
        $this->Id_user = $value;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($value)
    {
        $this->type = $value;
    }
}
