<?php

namespace App\Entity;

use App\Repository\EducationPlanRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EducationPlanRepository::class)]
class EducationPlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'educationPlans')]
    private ?Faculty $Faculty = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFaculty(): ?Faculty
    {
        return $this->Faculty;
    }

    public function setFaculty(?Faculty $Faculty): static
    {
        $this->Faculty = $Faculty;

        return $this;
    }
}
