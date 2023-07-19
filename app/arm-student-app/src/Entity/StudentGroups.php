<?php

namespace App\Entity;

use App\Repository\StudentGroupsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudentGroupsRepository::class)]
class StudentGroups
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 12)]
    private ?string $Name = null;

    #[ORM\Column(length: 255)]
    private ?string $Letter = null;

    #[ORM\ManyToOne(inversedBy: 'studentGroups')]
    private ?Faculty $Faculty = null;

    #[ORM\ManyToOne(inversedBy: 'studentGroups')]
    private ?Staff $GroupLeader = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Code = null;

    #[ORM\OneToMany(mappedBy: 'StudentGroup', targetEntity: Student::class)]
    private Collection $students;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $DateStart = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $DataEnd = null;

    #[ORM\Column( nullable: true)]
    private ?int $CourseNumber = null;





    public function __construct()
    {
        $this->students = new ArrayCollection();
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

    public function getLetter(): ?string
    {
        return $this->Letter;
    }

    public function setLetter(string $Letter): self
    {
        $this->Letter = $Letter;

        return $this;
    }

    public function getFaculty(): ?Faculty
    {
        return $this->Faculty;
    }

    public function setFaculty(?Faculty $Faculty): self
    {
        $this->Faculty = $Faculty;

        return $this;
    }

    public function getGroupLeader(): ?Staff
    {
        return $this->GroupLeader;
    }

    public function setGroupLeader(?Staff $GroupLeader): self
    {
        $this->GroupLeader = $GroupLeader;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->Code;
    }

    public function setCode(string $Code): self
    {
        $this->Code = $Code;

        return $this;
    }

    /**
     * @return Collection<int, Student>
     */
    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function addStudent(Student $student): self
    {
        if (!$this->students->contains($student)) {
            $this->students->add($student);
            $student->setStudentGroup($this);
        }

        return $this;
    }

    public function removeStudent(Student $student): self
    {
        if ($this->students->removeElement($student)) {
            // set the owning side to null (unless already changed)
            if ($student->getStudentGroup() === $this) {
                $student->setStudentGroup(null);
            }
        }

        return $this;
    }
    public function __toString(): string
    {
     return $this->getName().' ('.$this->getCode().')';
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->DateStart;
    }

    public function setDateStart(?\DateTimeInterface $DateStart): self
    {
        $this->DateStart = $DateStart;

        return $this;
    }

    public function getDataEnd(): ?\DateTimeInterface
    {
        return $this->DataEnd;
    }

    public function setDataEnd(?\DateTimeInterface $DataEnd): self
    {
        $this->DataEnd = $DataEnd;

        return $this;
    }

    public function getCourseNumber(): ?int
    {
        return $this->CourseNumber;
    }

    public function setCourseNumber(int $CourseNumber): self
    {
        $this->CourseNumber = $CourseNumber;

        return $this;
    }
}
