<?php

namespace App\Entity;

use App\Repository\CompanyMainUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: CompanyMainUserRepository::class)]
class CompanyMainUser implements UserInterface,PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $email;

    #[ORM\Column(type: 'string', length: 255)]
    private $password;


    #[ORM\Column(type: 'json')]
    private $roles;


    #[ORM\OneToOne(targetEntity: Company::class, cascade: ['persist', 'remove'])]
    private $company;

    #[ORM\ManyToOne(targetEntity: CompanyObject::class, inversedBy: 'mainUser')]
    #[ORM\JoinColumn(nullable: true)]
    private $companyObject;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

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


    public function getRoles():array
    {
        return $this->roles;
    }

    public function setRoles(string $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return $this->getId();
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

    public function getCompanyObject(): ?CompanyObject
    {
        return $this->companyObject;
    }

    public function setCompanyObject(?CompanyObject $companyObject): self
    {
        $this->companyObject = $companyObject;

        return $this;
    }



}
