<?php

namespace App\Entity;

use App\Repository\TimeEntryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TimeEntryRepository::class)]
class TimeEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime_immutable')]
    private $createdAt;

    #[ORM\ManyToOne(targetEntity: TimeEntryType::class, inversedBy: 'timeEntries')]
    private $timeEntryType;

    #[ORM\ManyToOne(targetEntity: CompanyUser::class, inversedBy: 'timeEntries')]
    #[ORM\JoinColumn(nullable: false)]
    private $employer;


    #[ORM\Column(type: 'boolean', nullable: true)]
    private $autoCheckOut;

    #[ORM\ManyToOne(targetEntity: CompanyObject::class, inversedBy: 'timeEntries')]
    #[ORM\JoinColumn(nullable: false)]
    private $object;

    #[ORM\Column(type: 'boolean')]
    private $mainCheckin;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $uid;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $removed;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getTimeEntryType(): ?TimeEntryType
    {
        return $this->timeEntryType;
    }

    public function setTimeEntryType(?TimeEntryType $timeEntryType): self
    {
        $this->timeEntryType = $timeEntryType;

        return $this;
    }

    public function getEmployer(): ?CompanyUser
    {
        return $this->employer;
    }

    public function setEmployer(?CompanyUser $employer): self
    {
        $this->employer = $employer;

        return $this;
    }

    public function getAutoCheckOut(): ?bool
    {
        return $this->autoCheckOut;
    }

    public function setAutoCheckOut(?bool $autoCheckOut): self
    {
        $this->autoCheckOut = $autoCheckOut;

        return $this;
    }

    public function getObject(): ?CompanyObject
    {
        return $this->object;
    }

    public function setObject(?CompanyObject $object): self
    {
        $this->object = $object;

        return $this;
    }

    public function getMainCheckin(): ?bool
    {
        return $this->mainCheckin;
    }

    public function setMainCheckin(bool $mainCheckin): self
    {
        $this->mainCheckin = $mainCheckin;

        return $this;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(?string $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    public function getRemoved(): ?bool
    {
        return $this->removed;
    }

    public function setRemoved(?bool $removed): self
    {
        $this->removed = $removed;

        return $this;
    }

}
