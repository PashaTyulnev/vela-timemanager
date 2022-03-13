<?php

namespace App\Service;

use App\Entity\TimeEntry;
use App\Repository\EmployerRepository;
use App\Repository\TimeEntryRepository;
use App\Repository\TimeEntryTypeRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;

class EmployerService
{

    private EmployerRepository $employerRepository;
    private TimeEntryTypeRepository $timeEntryTypeRepository;
    private EntityManagerInterface $entityManager;
    private $checkInType;
    private $employerId;
    private TimeEntryRepository $timeEntryRepository;
    private ?\App\Entity\TimeEntryType $timeEntryType;
    private DateTimeImmutable $date;

    public function __construct(EmployerRepository      $employerRepository,
                                TimeEntryTypeRepository $timeEntryTypeRepository,
                                EntityManagerInterface  $entityManager,
                                TimeEntryRepository     $timeEntryRepository
    )
    {

        $this->employerRepository = $employerRepository;
        $this->timeEntryTypeRepository = $timeEntryTypeRepository;
        $this->entityManager = $entityManager;
        $this->timeEntryRepository = $timeEntryRepository;
        //define now time for compare
        $this->date = new DateTimeImmutable();

    }

    public function getAllEmployers()
    {
        return $this->employerRepository->findAll();
    }

    public function getEmployerById($id)
    {
        return $this->employerRepository->findOneBy(['id' => $id]);
    }
    public function getEmployerByPin($pin)
    {
        return $this->employerRepository->findOneBy(['pin' => $pin]);
    }
    /**
     * @param $employer
     * @return DateTimeImmutable|null
     *
     * Return the time, when employer checked in last time in range of 12 hours
     */
    public function getEmployerWorkStartToday($employer): ?DateTimeImmutable
    {

        $todayRangeHours = 12;
        $lastCheckinOfEmployer = $this->getLastCheckinOfEmployer($employer);
        if($lastCheckinOfEmployer === null){
            return null;
        }
        else{
            //define what is "today", if in range of "today"
            if($this->timeDiffInMinutes($this->date->getTimestamp(),$lastCheckinOfEmployer->getTimestamp()) < $todayRangeHours*60){
                return $lastCheckinOfEmployer;
            }
        }

        return null;
    }

    /**
     * @param $id
     * @param $checkInType
     * @return TimeEntry
     *
     * Check in, check out oder Pause
     * @throws \Exception
     */
    public function userCheckAction($id, $checkInType)
    {

        $this->timeEntryType = $this->getTimeEntryTypeByName($checkInType);

        $timeEntry = new TimeEntry();

        $this->employerId = $id;
        $this->checkInType = $checkInType;

        //check if this action is valid
        $this->checkIfActionAllowed();

        $timeEntry->setEmployer($this->getEmployerById($id));
        $timeEntry->setTimeEntryType($this->timeEntryType);
        $timeEntry->setCreatedAt($this->date);
        $this->entityManager->persist($timeEntry);
        $this->entityManager->flush();

        return $timeEntry;

    }

    public function getTimeEntryTypeByName($name): ?\App\Entity\TimeEntryType
    {
        return $this->timeEntryTypeRepository->findOneBy(['name' => $name]);
    }

    /**
     * @throws \Exception
     */
    #[ArrayShape(['checkInActionMessage' => "null|string"])]
    private function checkIfActionAllowed(): int
    {

        //TODO Muss einstellbar sein
        $minMinutesShouldPass = 1;
        $lastTimeEntryOfEmployerSameType = $this->timeEntryRepository->findOneBy([
            'employer' => $this->employerId,
            'timeEntryType' => $this->timeEntryType
        ],
            [
                'createdAt' => 'desc'
            ]);

        $lastTimeEntryOfEmployer = $this->timeEntryRepository->findOneBy([
            'employer' => $this->employerId,
        ],
            [
                'createdAt' => 'desc'
            ]);


        //if no entry yet and checkin action then valid
        if ($lastTimeEntryOfEmployerSameType === null && $this->timeEntryType->getName() === "checkin") {
            return true;
        }

        //if just checked in
        $lastCheckInTime = $this->getLastCheckinOfEmployer($this->employerId);
        if ($this->timeEntryType->getName() === "checkout" && ($this->timeDiffInMinutes($this->date->getTimestamp(),$lastCheckInTime->getTimestamp()) < $minMinutesShouldPass)) {
            throw new \Exception("Du hast dich gerade erst eingecheckt. Bitte warte wenigstens ". $minMinutesShouldPass . " Minute(n)");
        }

        //if pause without checkin
        if($this->timeEntryType->getName() === "pause" && $lastTimeEntryOfEmployer->getTimeEntryType()->getName() !== "checkin"){
            throw new \Exception("Du musst dich zuerst einchecken");
        }

        //if checkout without checkin
        if($this->timeEntryType->getName() === "checkout" && $lastTimeEntryOfEmployer->getTimeEntryType()->getName() !== "checkin"){
            throw new \Exception("Du musst dich zuerst einchecken");
        }

        //if checkin after checkin
        if($this->timeEntryType->getName() === "checkin" && $lastTimeEntryOfEmployer->getTimeEntryType()->getName() === "checkin"){
            throw new \Exception("Du musst dich zuerst einchecken");
        }

        if($this->timeEntryType->getName() === "checkout" && $lastTimeEntryOfEmployer->getTimeEntryType()->getName() === "checkin"){
            return true;
        }

        $lastTimeEntryOfEmployerSameTypeCreated = $lastTimeEntryOfEmployerSameType->getCreatedAt();

        //check time since last same action minutes
        $lastSameActionTimeGone = abs($this->date->getTimestamp() - $lastTimeEntryOfEmployerSameTypeCreated->getTimestamp()) / 60;



        //if less than 1 minute of same action
        if ($lastSameActionTimeGone < $minMinutesShouldPass) {
            if ($this->timeEntryType->getName() === "checkin") {
                throw new \Exception("Du hast dich gerade erst eingecheckt, bitte warte noch " . $minMinutesShouldPass . " Minute(n)");
            }
            if ($this->timeEntryType->getName() === "pause") {
                throw new \Exception("Du befindest dich bereits in einer Pause, bitte checke zuerst ein.");
            }
            if ($this->timeEntryType->getName() === "checkout") {
                throw new \Exception("Du hast dich bereits ausgecheckt. Bitte Check dich wieder ein.");
            }

        }


        return true;

    }

    private function getLastCheckinOfEmployer($employer): ?DateTimeImmutable
    {
        $checkInType = $this->timeEntryTypeRepository->findOneBy(['name'=>'checkin']);
        $lastCheckInOfUser = $this->timeEntryRepository->findOneBy(['timeEntryType'=>$checkInType,'employer'=>$employer],['createdAt' => 'desc']);

        if($lastCheckInOfUser === null){
            return null;
        }
        else{
            return $lastCheckInOfUser->getCreatedAt();
        }

    }

    private function timeDiffInMinutes($date1,$date2): float|int
    {
        return abs($date1 - $date2) / 60;
    }


}