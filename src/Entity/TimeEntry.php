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

    #[ORM\ManyToOne(targetEntity: Employer::class, inversedBy: 'timeEntries')]
    #[ORM\JoinColumn(nullable: false)]
    private $employer;

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

    public function getEmployer(): ?Employer
    {
        return $this->employer;
    }

    public function setEmployer(?Employer $employer): self
    {
        $this->employer = $employer;

        return $this;
    }
}
