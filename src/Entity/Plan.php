<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Objectif;

#[ORM\Entity]
class Plan
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_plan;

        #[ORM\ManyToOne(targetEntity: Objectif::class, inversedBy: "plans")]
    #[ORM\JoinColumn(name: 'id_obj', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Objectif $id_obj;

    #[ORM\Column(type: "string", length: 20)]
    private string $jour;

    #[ORM\Column(type: "string", length: 20)]
    private string $nutration_protein;

    #[ORM\Column(type: "float")]
    private float $course_kilometrage;

    #[ORM\Column(type: "string", length: 20)]
    private string $muscle;

    public function getId_plan()
    {
        return $this->id_plan;
    }

    public function setId_plan($value)
    {
        $this->id_plan = $value;
    }

    public function getId_obj()
    {
        return $this->id_obj;
    }

    public function setId_obj($value)
    {
        $this->id_obj = $value;
    }

    public function getJour()
    {
        return $this->jour;
    }

    public function setJour($value)
    {
        $this->jour = $value;
    }

    public function getNutration_protein()
    {
        return $this->nutration_protein;
    }

    public function setNutration_protein($value)
    {
        $this->nutration_protein = $value;
    }

    public function getCourse_kilometrage()
    {
        return $this->course_kilometrage;
    }

    public function setCourse_kilometrage($value)
    {
        $this->course_kilometrage = $value;
    }

    public function getMuscle()
    {
        return $this->muscle;
    }

    public function setMuscle($value)
    {
        $this->muscle = $value;
    }
}
