<?php

namespace App\mod_mosregvis\Entity;

use App\MainApp\Entity\College;
use App\MainApp\Repository\CollegeRepository;
use App\MainApp\Repository\ModMosregVisRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CollegeRepository::class)]
class MosregVISCollege extends College
{
    #[ORM\OneToMany(mappedBy: 'mosregVISCollege', targetEntity: modMosregVis::class)]
    private Collection $modmosregvis;

    public function __construct()
    {
        parent::__construct();
        $this->modmosregvis = new ArrayCollection();
    }

    /**
     * @return Collection<int, modMosregVis>
     */
    public function getModMosregVIS(): Collection
    {
        return $this->modmosregvis;
    }

    public function addModMosregVIS(modMosregVis $modmosregvis): static
    {

        if (!$this->modmosregvis->contains($modmosregvis)) {
            $this->modmosregvis->add($modmosregvis);
            $modmosregvis->setCollege($this);
        }
        return $this;
    }
    public function removeModMosregVIS(modMosregVis $modmosregvis): static
    {
        //TODO: make this method
        return $this;
    }
    public function __toString(): string
    {
        return parent::__toString();
    }

}