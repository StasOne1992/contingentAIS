<?php

namespace App\Entity;

use App\Repository\EducationTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EducationTypeRepository::class)]
class EducationType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Name = null;

    #[ORM\OneToMany(mappedBy: 'EducationType', targetEntity: Faculty::class)]
    private Collection $faculties;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\OneToMany(mappedBy: 'BaseEducationType', targetEntity: EducationPlan::class)]
    private Collection $educationPlans;

    public function __construct()
    {
        $this->faculties = new ArrayCollection();
        $this->educationPlans = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    /**
     * @return Collection<int, Faculty>
     */
    public function getFaculties(): Collection
    {
        return $this->faculties;
    }

    public function addFaculty(Faculty $faculty): self
    {
        if (!$this->faculties->contains($faculty)) {
            $this->faculties->add($faculty);
            $faculty->setEducationType($this);
        }

        return $this;
    }

    public function removeFaculty(Faculty $faculty): self
    {
        if ($this->faculties->removeElement($faculty)) {
            // set the owning side to null (unless already changed)
            if ($faculty->getEducationType() === $this) {
                $faculty->setEducationType(null);
            }
        }

        return $this;
    }
    public function __toString(): string
    {
     return $this->Name;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection<int, EducationPlan>
     */
    public function getEducationPlans(): Collection
    {
        return $this->educationPlans;
    }

    public function addEducationPlan(EducationPlan $educationPlan): static
    {
        if (!$this->educationPlans->contains($educationPlan)) {
            $this->educationPlans->add($educationPlan);
            $educationPlan->setBaseEducationType($this);
        }

        return $this;
    }

    public function removeEducationPlan(EducationPlan $educationPlan): static
    {
        if ($this->educationPlans->removeElement($educationPlan)) {
            // set the owning side to null (unless already changed)
            if ($educationPlan->getBaseEducationType() === $this) {
                $educationPlan->setBaseEducationType(null);
            }
        }

        return $this;
    }
}
