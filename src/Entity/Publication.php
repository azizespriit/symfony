<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Reactions;

#[ORM\Entity]
class Publication
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $Id_pub;

    #[ORM\Column(type: "integer")]
    private int $id_user;

    #[ORM\Column(type: "string", length: 130)]
    private string $imageUrl;

    #[ORM\Column(type: "text")]
    private string $contenu;

    #[ORM\Column(type: "string", length: 10000)]
    private string $description;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_pub;

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

    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    public function setImageUrl($value)
    {
        $this->imageUrl = $value;
    }

    public function getContenu()
    {
        return $this->contenu;
    }

    public function setContenu($value)
    {
        $this->contenu = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getDate_pub()
    {
        return $this->date_pub;
    }

    public function setDate_pub($value)
    {
        $this->date_pub = $value;
    }

    #[ORM\OneToMany(mappedBy: "Id_pub", targetEntity: Commentaire::class)]
    private Collection $commentaires;

        public function getCommentaires(): Collection
        {
            return $this->commentaires;
        }
    
        public function addCommentaire(Commentaire $commentaire): self
        {
            if (!$this->commentaires->contains($commentaire)) {
                $this->commentaires[] = $commentaire;
                $commentaire->setId_pub($this);
            }
    
            return $this;
        }
    
        public function removeCommentaire(Commentaire $commentaire): self
        {
            if ($this->commentaires->removeElement($commentaire)) {
                // set the owning side to null (unless already changed)
                if ($commentaire->getId_pub() === $this) {
                    $commentaire->setId_pub(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "Id_pub", targetEntity: Reactions::class)]
    private Collection $reactionss;
}
