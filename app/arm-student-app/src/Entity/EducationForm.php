<?php

namespace App\Entity;

use App\Repository\EducationFormRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EducationFormRepository::class)]
class EducationForm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Name = null;

    #[ORM\OneToMany(mappedBy: 'EducationForm', targetEntity: Faculty::class)]
    private Collection $faculties;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    public function __construct()
    {
        $this->faculties = new ArrayCollection();
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
            $faculty->setEducationForm($this);
        }

        return $this;
    }

    public function removeFaculty(Faculty $faculty): self
    {
        if ($this->faculties->removeElement($faculty)) {
            // set the owning side to null (unless already changed)
            if ($faculty->getEducationForm() === $this) {
                $faculty->setEducationForm(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
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
}
