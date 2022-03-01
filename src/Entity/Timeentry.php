<?php

namespace App\Entity;

use App\Repository\TimeentryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TimeentryRepository::class)]
class Timeentry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
