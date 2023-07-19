<?php

namespace App\Entity;

use App\Repository\AdmissionPlanRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdmissionPlanRepository::class)]
class AdmissionPlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'admissionPlans')]
    private ?Admission $admission = null;

    #[ORM\Column(nullable: true)]
    private ?int $TargetCount = null;


    #[ORM\ManyToOne(inversedBy: 'admissionPlans')]
    private ?Faculty $faculty = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdmission(): ?Admission
    {
        return $this->admission;
    }

    public function setAdmission(?Admission $admission): static
    {
        $this->admission = $admission;

        return $this;
    }

    public function getTargetCount(): ?int
    {
        return $this->TargetCount;
    }

    public function setTargetCount(?int $TargetCount): static
    {
        $this->TargetCount = $TargetCount;

        return $this;
    }


    public function getFaculty(): ?Faculty
    {
        return $this->faculty;
    }

    public function setFaculty(?Faculty $faculty): static
    {
        $this->faculty = $faculty;

        return $this;
    }
}
