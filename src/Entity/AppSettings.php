<?php

namespace App\Entity;

use App\Repository\AppSettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppSettingsRepository::class)]
class AppSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'boolean')]
    private $authOnlyByPin;

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
}
