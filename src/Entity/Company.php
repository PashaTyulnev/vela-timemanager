<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 255)]
    private $code;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: CompanyAppSettings::class)]
    private $companyAppSettings;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: CompanyMainUser::class)]
    private $companyMainUsers;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: CompanyUser::class)]
    private $companyUsers;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: TimeEntry::class)]
    private $timeEntries;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: CompanyObject::class)]
    private $companyObjects;

    public function __construct()
    {
        $this->companyObjects = new ArrayCollection();
        $this->companyAppSettings = new ArrayCollection();
        $this->companyMainUsers = new ArrayCollection();
        $this->companyUsers = new ArrayCollection();
        $this->timeEntries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection<int, CompanyAppSettings>
     */
    public function getCompanyAppSettings(): Collection
    {
        return $this->companyAppSettings;
    }

    public function addCompanyAppSetting(CompanyAppSettings $companyAppSetting): self
    {
        if (!$this->companyAppSettings->contains($companyAppSetting)) {
            $this->companyAppSettings[] = $companyAppSetting;
            $companyAppSetting->setCompany($this);
        }

        return $this;
    }

    public function removeCompanyAppSetting(CompanyAppSettings $companyAppSetting): self
    {
        if ($this->companyAppSettings->removeElement($companyAppSetting)) {
            // set the owning side to null (unless already changed)
            if ($companyAppSetting->getCompany() === $this) {
                $companyAppSetting->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CompanyMainUser>
     */
    public function getCompanyMainUsers(): Collection
    {
        return $this->companyMainUsers;
    }

    public function addCompanyMainUser(CompanyMainUser $companyMainUser): self
    {
        if (!$this->companyMainUsers->contains($companyMainUser)) {
            $this->companyMainUsers[] = $companyMainUser;
            $companyMainUser->setCompany($this);
        }

        return $this;
    }

    public function removeCompanyMainUser(CompanyMainUser $companyMainUser): self
    {
        if ($this->companyMainUsers->removeElement($companyMainUser)) {
            // set the owning side to null (unless already changed)
            if ($companyMainUser->getCompany() === $this) {
                $companyMainUser->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CompanyUser>
     */
    public function getCompanyUsers(): Collection
    {
        return $this->companyUsers;
    }

    public function addCompanyUser(CompanyUser $companyUser): self
    {
        if (!$this->companyUsers->contains($companyUser)) {
            $this->companyUsers[] = $companyUser;
            $companyUser->setCompany($this);
        }

        return $this;
    }

    public function removeCompanyUser(CompanyUser $companyUser): self
    {
        if ($this->companyUsers->removeElement($companyUser)) {
            // set the owning side to null (unless already changed)
            if ($companyUser->getCompany() === $this) {
                $companyUser->setCompany(null);
            }
        }

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
            $timeEntry->setCompany($this);
        }

        return $this;
    }

    public function removeTimeEntry(TimeEntry $timeEntry): self
    {
        if ($this->timeEntries->removeElement($timeEntry)) {
            // set the owning side to null (unless already changed)
            if ($timeEntry->getCompany() === $this) {
                $timeEntry->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CompanyObject>
     */
    public function getCompanyObjects(): Collection
    {
        return $this->companyObjects;
    }

    public function addCompanyObject(CompanyObject $companyObject): self
    {
        if (!$this->companyObjects->contains($companyObject)) {
            $this->companyObjects[] = $companyObject;
            $companyObject->setCompany($this);
        }

        return $this;
    }

    public function removeCompanyObject(CompanyObject $companyObject): self
    {
        if ($this->companyObjects->removeElement($companyObject)) {
            // set the owning side to null (unless already changed)
            if ($companyObject->getCompany() === $this) {
                $companyObject->setCompany(null);
            }
        }

        return $this;
    }
}
