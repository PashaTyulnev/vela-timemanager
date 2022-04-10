<?php

namespace App\Entity;

use App\Repository\CompanyAppSettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyAppSettingsRepository::class)]
class CompanyAppSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'boolean')]
    private $authOnlyByPin;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'companyAppSettings')]
    #[ORM\JoinColumn(nullable: false)]
    private $company;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $autoCheckoutAfterHours;

    #[ORM\Column(type: 'integer')]
    private $autoCheckoutGiveHours;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthOnlyByPin(): ?bool
    {
        return $this->authOnlyByPin;
    }

    public function setAuthOnlyByPin(bool $authOnlyByPin): self
    {
        $this->authOnlyByPin = $authOnlyByPin;

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

    public function getAutoCheckoutAfterHours(): ?int
    {
        return $this->autoCheckoutAfterHours;
    }

    public function setAutoCheckoutAfterHours(?int $autoCheckoutAfterHours): self
    {
        $this->autoCheckoutAfterHours = $autoCheckoutAfterHours;

        return $this;
    }

    public function getAutoCheckoutGiveHours(): ?int
    {
        return $this->autoCheckoutGiveHours;
    }

    public function setAutoCheckoutGiveHours(int $autoCheckoutGiveHours): self
    {
        $this->autoCheckoutGiveHours = $autoCheckoutGiveHours;

        return $this;
    }
}