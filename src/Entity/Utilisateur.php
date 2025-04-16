<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Reactions;

#[ORM\Entity]
class Utilisateur
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $Id_user;

    #[ORM\Column(type: "string", length: 100)]
    private string $nom;

    #[ORM\Column(type: "string", length: 150)]
    private string $email;

    #[ORM\Column(type: "string", length: 255)]
    private string $mot_de_passe;

    #[ORM\Column(type: "string", length: 255)]
    private string $role;

    #[ORM\Column(type: "string", length: 30)]
    private string $prenom;

    public function getId_user()
    {
        return $this->Id_user;
    }

    public function setId_user($value)
    {
        $this->Id_user = $value;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($value)
    {
        $this->nom = $value;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($value)
    {
        $this->email = $value;
    }

    public function getMot_de_passe()
    {
        return $this->mot_de_passe;
    }

    public function setMot_de_passe($value)
    {
        $this->mot_de_passe = $value;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($value)
    {
        $this->role = $value;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }

    public function setPrenom($value)
    {
        $this->prenom = $value;
    }

    #[ORM\OneToMany(mappedBy: "Id_user", targetEntity: Reactions::class)]
    private Collection $reactionss;

        public function getReactionss(): Collection
        {
            return $this->reactionss;
        }
    
        public function addReactions(Reactions $reactions): self
        {
            if (!$this->reactionss->contains($reactions)) {
                $this->reactionss[] = $reactions;
                $reactions->setId_user($this);
            }
    
            return $this;
        }
    
        public function removeReactions(Reactions $reactions): self
        {
            if ($this->reactionss->removeElement($reactions)) {
                // set the owning side to null (unless already changed)
                if ($reactions->getId_user() === $this) {
                    $reactions->setId_user(null);
                }
            }
    
            return $this;
        }
}
