<?php

namespace App\Security;

use App\Entity\TimeEntry;
use App\Repository\EmployerRepository;
use App\Repository\TimeEntryTypeRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class EmployerService
{

    private EmployerRepository $employerRepository;
    private TimeEntryTypeRepository $timeEntryTypeRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(EmployerRepository $employerRepository, TimeEntryTypeRepository $timeEntryTypeRepository, EntityManagerInterface $entityManager){

        $this->employerRepository = $employerRepository;
        $this->timeEntryTypeRepository = $timeEntryTypeRepository;
        $this->entityManager = $entityManager;
    }
    public function getAllEmployers()
    {
        return $this->employerRepository->findAll();
    }

    public function getEmployerById($id)
    {
        return $this->employerRepository->find(['id'=>$id]);
    }

    public function userCheckAction($id,$checkInType){

        $timeEntryType = $this->getTimeEntryTypeByName($checkInType);

        $date = new DateTimeImmutable();
        $timeEntry = new TimeEntry();
        $timeEntry->setEmployer($this->getEmployerById($id));
        $timeEntry->setTimeEntryType($timeEntryType);
        $timeEntry->setCreatedAt($date);
        $this->entityManager->persist($timeEntry);
        $this->entityManager->flush();

        return $timeEntry;

    }

    public function getTimeEntryTypeByName($name): ?\App\Entity\TimeEntryType
    {
        return $this->timeEntryTypeRepository->findOneBy(['name'=>$name]);
    }
}