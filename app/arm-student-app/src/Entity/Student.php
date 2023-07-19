<?php

namespace App\Entity;

use App\Form\EduPart\StudentType;
use App\Repository\StudentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Boolean;
use PhpParser\Node\Expr\New_;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
class Student
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $NumberZachetka = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $NumberStudBilet = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $BirthData = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $PhoneNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $FirstName = null;

    #[ORM\Column(length: 255)]
    private ?string $LastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $MiddleName = null;

    #[ORM\Column(nullable: true)]
    private ?float $DocumentSnils = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $DocumentMedicalID = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $AddressFact = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $AddressMain = null;

    #[ORM\ManyToOne(inversedBy: 'students')]
    private ?FamilyTypeList $FamilyTypeID = null;

    #[ORM\ManyToOne(inversedBy: 'students')]
    private ?HealthGroup $HealtgGroupID = null;

    #[ORM\OneToMany(mappedBy: 'StudentID', targetEntity: LegalRepresentative::class)]
    private Collection $legalRepresentatives;

    #[ORM\OneToMany(mappedBy: 'StudetnID', targetEntity: SocialNetwork::class)]
    private Collection $socialNetworks;

    #[ORM\OneToMany(mappedBy: 'Student', targetEntity: PersonalDocuments::class)]
    private Collection $personalDocuments;

    #[ORM\ManyToOne(inversedBy: 'students')]
    private ?PersonaSex $Sex = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isActive = null;

    #[ORM\OneToMany(mappedBy: 'Student_ID', targetEntity: User::class)]
    private Collection $users;


    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $Photo = null;

    #[ORM\ManyToOne(inversedBy: 'students')]
    private ?StudentGroups $StudentGroup = null;

    #[ORM\Column(nullable: true)]
    private ?bool $IsOrphan = null;

    #[ORM\Column(nullable: true)]
    private ?bool $IsPaid = null;

    #[ORM\Column(nullable: true)]
    private ?bool $IsInvalid = null;

    #[ORM\Column(nullable: true)]
    private ?bool $IsPoor = null;

    #[ORM\Column(nullable: true)]
    private ?int $PasportNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $PasportSeries = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $PasportDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $PasportIssueOrgan = null;

    #[ORM\OneToMany(mappedBy: 'Student', targetEntity: Characteristic::class)]
    private Collection $characteristics;

    #[ORM\Column(nullable: true)]
    private ?bool $isWithoutParents = null;

    #[ORM\ManyToMany(targetEntity: ContingentDocument::class, mappedBy: 'student')]
    private Collection $contingentDocuments;


    public function __construct()
    {
        $this->legalRepresentatives = new ArrayCollection();
        $this->socialNetworks = new ArrayCollection();
        $this->personalDocuments = new ArrayCollection();
        $this->personalDocuments->getValues();
        $this->users = new ArrayCollection();
        $this->characteristics = new ArrayCollection();
        $this->contingentDocuments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumberZachetka(): ?string
    {
        return $this->NumberZachetka;
    }

    public function setNumberZachetka(?string $NumberZachetka): self
    {
        $this->NumberZachetka = $NumberZachetka;

        return $this;
    }

    public function getNumberStudBilet(): ?string
    {
        return $this->NumberStudBilet;
    }

    public function setNumberStudBilet(?string $NumberStudBilet): self
    {
        $this->NumberStudBilet = $NumberStudBilet;

        return $this;
    }

    public function getBirthData(): ?\DateTimeInterface
    {

        return $this->BirthData;
    }

    public function setBirthData(?\DateTimeInterface $BirthData): self
    {
        $this->BirthData = $BirthData;

        return $this;
    }

    public function getPhoneNumber(): ?int
    {
        return $this->PhoneNumber;
    }

    public function setPhoneNumber(int $PhoneNumber): self
    {
        $this->PhoneNumber = $PhoneNumber;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstName(): ?string
    {

        return $this->FirstName;
    }

    public function setFirstName(string $FirstName): self
    {
        $this->FirstName = $FirstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->LastName;
    }

    public function setLastName(string $LastName): self
    {
        $this->LastName = $LastName;

        return $this;
    }

    public function getMiddleName(): ?string
    {
        return $this->MiddleName;
    }

    public function getFullName(): ?string
    {
        return $this->getLastName() . " " . $this->getFirstName() . " " . $this->getMiddleName();
    }

    public function setMiddleName(?string $MiddleName): self
    {
        $this->MiddleName = $MiddleName;

        return $this;
    }

    public function getDocumentSnils(): ?int
    {
        return $this->DocumentSnils;
    }

    public function setDocumentSnils(?int $DocumentSnils): self
    {
        $this->DocumentSnils = $DocumentSnils;

        return $this;
    }

    public function getDocumentMedicalID(): ?string
    {
        return $this->DocumentMedicalID;
    }

    public function setDocumentMedicalID(?string $DocumentMedicalID): self
    {
        $this->DocumentMedicalID = $DocumentMedicalID;

        return $this;
    }

    public function getAddressFact(): ?string
    {
        return $this->AddressFact;
    }

    public function setAddressFact(?string $AddressFact): self
    {
        $this->AddressFact = $AddressFact;

        return $this;
    }

    public function getAddressMain(): ?string
    {
        return $this->AddressMain;
    }

    public function setAddressMain(?string $AddressMain): self
    {
        $this->AddressMain = $AddressMain;

        return $this;
    }

    public function getFamilyTypeID(): ?FamilyTypeList
    {
        return $this->FamilyTypeID;
    }

    public function setFamilyTypeID(?FamilyTypeList $FamilyTypeID): self
    {
        $this->FamilyTypeID = $FamilyTypeID;

        return $this;
    }

    public function getHealtgGroupID(): ?HealthGroup
    {
        return $this->HealtgGroupID;
    }

    public function setHealtgGroupID(?HealthGroup $HealtgGroupID): self
    {
        $this->HealtgGroupID = $HealtgGroupID;

        return $this;
    }

    /**
     * @return Collection<int, LegalRepresentative>
     */
    public function getLegalRepresentatives(): Collection
    {
        return $this->legalRepresentatives;
    }

    public function addLegalRepresentative(LegalRepresentative $legalRepresentative): self
    {
        if (!$this->legalRepresentatives->contains($legalRepresentative)) {
            $this->legalRepresentatives->add($legalRepresentative);
            $legalRepresentative->setStudentID($this);
        }

        return $this;
    }

    public function removeLegalRepresentative(LegalRepresentative $legalRepresentative): self
    {
        if ($this->legalRepresentatives->removeElement($legalRepresentative)) {
            // set the owning side to null (unless already changed)
            if ($legalRepresentative->getStudentID() === $this) {
                $legalRepresentative->setStudentID(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SocialNetwork>
     */
    public function getSocialNetworks(): Collection
    {
        return $this->socialNetworks;
    }

    public function addSocialNetwork(SocialNetwork $socialNetwork): self
    {
        if (!$this->socialNetworks->contains($socialNetwork)) {
            $this->socialNetworks->add($socialNetwork);
            $socialNetwork->setStudetnID($this);
        }

        return $this;
    }

    public function removeSocialNetwork(SocialNetwork $socialNetwork): self
    {
        if ($this->socialNetworks->removeElement($socialNetwork)) {
            // set the owning side to null (unless already changed)
            if ($socialNetwork->getStudetnID() === $this) {
                $socialNetwork->setStudetnID(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PersonalDocuments>
     */
    public function getPersonalDocuments(): Collection
    {
        return $this->personalDocuments;
    }

    public function addPersonalDocument(PersonalDocuments $personalDocument): self
    {
        if (!$this->personalDocuments->contains($personalDocument)) {
            $this->personalDocuments->add($personalDocument);
            $personalDocument->setStudent($this);
        }

        return $this;
    }

    public function removePersonalDocument(PersonalDocuments $personalDocument): self
    {
        if ($this->personalDocuments->removeElement($personalDocument)) {
            // set the owning side to null (unless already changed)
            if ($personalDocument->getStudent() === $this) {
                $personalDocument->setStudent(null);
            }
        }

        return $this;
    }

    public function getSex(): ?PersonaSex
    {
        return $this->Sex;
    }

    public function setSex(?PersonaSex $Sex): self
    {
        $this->Sex = $Sex;

        return $this;
    }

    public function __toString()
    {
        return (string)$this->getFirstName() . ' ' . $this->getLastName() . ' ' . $this->getMiddleName();
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setStudentID($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getStudentID() === $this) {
                $user->setStudentID(null);
            }
        }

        return $this;
    }


    public function getPhoto(): ?string
    {
        return $this->Photo;
    }

    public function setPhoto(?string $Photo): self
    {
        $this->Photo = $Photo;

        return $this;
    }

    public function getStudentGroup(): ?StudentGroups
    {
        return $this->StudentGroup;
    }

    public function setStudentGroup(?StudentGroups $StudentGroup): self
    {
        $this->StudentGroup = $StudentGroup;

        return $this;
    }

    public function isIsOrphan(): ?bool
    {
        return $this->IsOrphan;
    }

    public function setIsOrphan(?bool $IsOrphan): self
    {
        $this->IsOrphan = $IsOrphan;

        return $this;
    }

    public function isIsPaid(): ?bool
    {
        return $this->IsPaid;
    }

    public function setIsPaid(?bool $IsPaid): self
    {
        $this->IsPaid = $IsPaid;

        return $this;
    }

    public function isIsInvalid(): ?bool
    {
        return $this->IsInvalid;
    }

    public function setIsInvalid(?bool $IsInvalid): self
    {
        $this->IsInvalid = $IsInvalid;

        return $this;
    }

    public function isIsPoor(): ?bool
    {
        return $this->IsPoor;
    }

    public function setIsPoor(?bool $IsPoor): self
    {
        $this->IsPoor = $IsPoor;

        return $this;
    }

    public function getPasportNumber(): ?int
    {
        return $this->PasportNumber;
    }

    public function setPasportNumber(?int $PasportNumber): self
    {
        $this->PasportNumber = $PasportNumber;

        return $this;
    }

    public function getPasportSeries(): ?string
    {
        return $this->PasportSeries;
    }

    public function setPasportSeries(string $PasportSeries): self
    {
        $this->PasportSeries = $PasportSeries;

        return $this;
    }

    public function getPasportDate(): ?\DateTimeInterface
    {
        return $this->PasportDate;
    }

    public function setPasportDate(\DateTimeInterface $PasportDate): self
    {
        $this->PasportDate = $PasportDate;

        return $this;
    }

    public function getPasportIssueOrgan(): ?string
    {
        return $this->PasportIssueOrgan;
    }

    public function setPasportIssueOrgan(?string $PasportIssueOrgan): self
    {
        $this->PasportIssueOrgan = $PasportIssueOrgan;

        return $this;
    }

    /**
     * @return Collection<int, Characteristic>
     */
    public function getCharacteristics(): Collection
    {
        return $this->characteristics;
    }

    public function addCharacteristic(Characteristic $characteristic): self
    {
        if (!$this->characteristics->contains($characteristic)) {
            $this->characteristics->add($characteristic);
            $characteristic->setStudent($this);
        }

        return $this;
    }

    public function removeCharacteristic(Characteristic $characteristic): self
    {
        if ($this->characteristics->removeElement($characteristic)) {
            // set the owning side to null (unless already changed)
            if ($characteristic->getStudent() === $this) {
                $characteristic->setStudent(null);
            }
        }

        return $this;
    }

    public function isIsWithoutParents(): ?bool
    {
        return $this->isWithoutParents;
    }

    public function setIsWithoutParents(?bool $isWithoutParents): self
    {
        $this->isWithoutParents = $isWithoutParents;

        return $this;
    }

    /**
     * @return Collection<int, ContingentDocument>
     */
    public function getContingentDocuments(): Collection
    {
        return $this->contingentDocuments;
    }

    public function addContingentDocument(ContingentDocument $contingentDocument): self
    {
        if (!$this->contingentDocuments->contains($contingentDocument)) {
            $this->contingentDocuments->add($contingentDocument);
            $contingentDocument->addStudent($this);
        }

        return $this;
    }

    public function removeContingentDocument(ContingentDocument $contingentDocument): self
    {
        if ($this->contingentDocuments->removeElement($contingentDocument)) {
            $contingentDocument->removeStudent($this);
        }

        return $this;
    }

}
