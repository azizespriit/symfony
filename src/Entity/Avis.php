<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Client;

#[ORM\Entity]
class Avis
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $ID;

    #[ORM\Column(type: "string", length: 400)]
    private string $Content;

        #[ORM\ManyToOne(targetEntity: Service::class, inversedBy: "aviss")]
    #[ORM\JoinColumn(name: 'ServiceID', referencedColumnName: 'ID', onDelete: 'CASCADE')]
    private Service $ServiceID;

        #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: "aviss")]
    #[ORM\JoinColumn(name: 'OrganisateurID', referencedColumnName: 'id_client', onDelete: 'CASCADE')]
    private Client $OrganisateurID;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $CreatedAt;

    public function getID()
    {
        return $this->ID;
    }

    public function setID($value)
    {
        $this->ID = $value;
    }

    public function getContent()
    {
        return $this->Content;
    }

    public function setContent($value)
    {
        $this->Content = $value;
    }

    public function getServiceID()
    {
        return $this->ServiceID;
    }

    public function setServiceID($value)
    {
        $this->ServiceID = $value;
    }

    public function getOrganisateurID()
    {
        return $this->OrganisateurID;
    }

    public function setOrganisateurID($value)
    {
        $this->OrganisateurID = $value;
    }

    public function getCreatedAt()
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt($value)
    {
        $this->CreatedAt = $value;
    }
}
