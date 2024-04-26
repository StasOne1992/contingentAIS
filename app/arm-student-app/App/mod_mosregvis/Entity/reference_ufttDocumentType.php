<?php

namespace App\mod_mosregvis\Entity;

use App\mod_mosregvis\Repository\reference_ufttDocumentTypeRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: reference_ufttDocumentTypeRepository::class)]
class reference_ufttDocumentType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $code = null;

    #[ORM\Column(length: 255)]
    private  ?int $title = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(?int $code): void
    {
        $this->code = $code;
    }

    public function getTitle(): ?int
    {
        return $this->title;
    }

    public function setTitle(?int $title): void
    {
        $this->title = $title;
    }


}