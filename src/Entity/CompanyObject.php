<?php

namespace App\Entity;

use App\Repository\CompanyObjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyObjectRepository::class)]
class CompanyObject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;


    #[ORM\OneToMany(mappedBy: 'companyObject', targetEntity: CompanyMainUser::class)]
    private $mainUser;

    #[ORM\ManyToOne(targetEntity: company::class, inversedBy: 'companyObjects')]
    private $company;

    #[ORM\OneToMany(mappedBy: 'object', targetEntity: TimeEntry::class)]
    private $timeEntries;

    #[ORM\Column(type: 'string', length: 255)]
    private $street;

    #[ORM\Column(type: 'string', length: 255)]
    private $number;

    #[ORM\Column(type: 'string', length: 255)]
    private $password;

    #[ORM\Column(type: 'string', length: 255)]
    private $shortName;

    public function __construct()
    {
        $this->mainUser = new ArrayCollection();
        $this->timeEntries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    /**
     * @return Collection<int, companyMainUser>
     */
    public function getMainUser(): Collection
    {
        return $this->mainUser;
    }

    public function addMainUser(companyMainUser $mainUser): self
    {
        if (!$this->mainUser->contains($mainUser)) {
            $this->mainUser[] = $mainUser;
            $mainUser->setCompanyObject($this);
        }

        return $this;
    }

    public function removeMainUser(companyMainUser $mainUser): self
    {
        if ($this->mainUser->removeElement($mainUser)) {
            // set the owning side to null (unless already changed)
            if ($mainUser->getCompanyObject() === $this) {
                $mainUser->setCompanyObject(null);
            }
        }

        return $this;
    }

    public function getCompany(): ?company
    {
        return $this->company;
    }

    public function setCompany(?company $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection<int, TimeEntry>
     */
    public function getTimeEntries(): Collection
    {
        return $this->timeEntries;
    }

    public function addTimeEntry(TimeEntry $timeEntry): self
    {
        if (!$this->timeEntries->contains($timeEntry)) {
            $this->timeEntries[] = $timeEntry;
            $timeEntry->setObject($this);
        }

        return $this;
    }

    public function removeTimeEntry(TimeEntry $timeEntry): self
    {
        if ($this->timeEntries->removeElement($timeEntry)) {
            // set the owning side to null (unless already changed)
            if ($timeEntry->getObject() === $this) {
                $timeEntry->setObject(null);
            }
        }

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }
}
