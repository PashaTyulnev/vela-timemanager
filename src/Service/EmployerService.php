<?php

namespace App\Service;

use App\Entity\CompanyAppSettings;
use App\Entity\TimeEntry;
use App\Repository\CompanyAppSettingsRepository;
use App\Repository\CompanyUserRepository;
use App\Repository\TimeEntryRepository;
use App\Repository\TimeEntryTypeRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;

class EmployerService
{

    private CompanyUserRepository $employerRepository;
    private TimeEntryTypeRepository $timeEntryTypeRepository;
    private EntityManagerInterface $entityManager;
    private int $employerId;
    private TimeEntryRepository $timeEntryRepository;
    private ?\App\Entity\TimeEntryType $timeEntryType;
    private DateTimeImmutable $date;
    private CompanyService $companyService;
    private CompanyAppSettingsRepository $companyAppSettingsRepository;


    public function __construct(CompanyUserRepository        $employerRepository,
                                TimeEntryTypeRepository      $timeEntryTypeRepository,
                                EntityManagerInterface       $entityManager,
                                TimeEntryRepository          $timeEntryRepository,
                                CompanyService               $companyService,
                                CompanyAppSettingsRepository $companyAppSettingsRepository,
    )
    {

        $this->employerRepository = $employerRepository;
        $this->timeEntryTypeRepository = $timeEntryTypeRepository;
        $this->entityManager = $entityManager;
        $this->timeEntryRepository = $timeEntryRepository;
        $this->companyService = $companyService;

        //define now time for compare
        $this->date = new DateTimeImmutable();
        $this->companyAppSettingsRepository = $companyAppSettingsRepository;

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
//        $lastCheckoutOfEmployer = $this->getLastCheckinOfEmployer($employer,'checkout');

        if ($lastCheckinOfEmployer === null) {
            return null;
        } else {
            //define what is "today", if in range of "today"
            if ($this->timeDiffInMinutes($this->date->getTimestamp(), $lastCheckinOfEmployer->getTimestamp()) < $todayRangeHours * 60) {
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
    public function userCheckAction($id, $checkInType,$autoCheckoutTime = 0)
    {

        $this->timeEntryType = $this->getTimeEntryTypeByName($checkInType);

        $timeEntry = new TimeEntry();

        $this->employerId = $id;
        $this->checkInType = $checkInType;

        if($autoCheckoutTime === 0){
            //check if this action is valid
            $this->checkIfActionAllowed();
        }

        $timeEntry->setEmployer($this->getEmployerById($id));
        $timeEntry->setTimeEntryType($this->timeEntryType);

        if($autoCheckoutTime !== 0){
            $timeEntry->setCreatedAt($autoCheckoutTime);
        }else{
            $timeEntry->setCreatedAt($this->date);
        }
        $timeEntry->setObject($this->companyService->getCurrentObject());
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
        if ($this->timeEntryType->getName() === "checkout" && ($this->timeDiffInMinutes($this->date->getTimestamp(), $lastCheckInTime->getTimestamp()) < $minMinutesShouldPass)) {
            throw new \Exception("Du hast dich gerade erst eingecheckt. Bitte warte wenigstens " . $minMinutesShouldPass . " Minute(n)");
        }

        //if pause without checkin
        if ($this->timeEntryType->getName() === "pause" && $lastTimeEntryOfEmployer->getTimeEntryType()->getName() !== "checkin") {
            throw new \Exception("Du musst dich zuerst einchecken Code:1");
        }

        //if checkout without checkin
        if ($this->timeEntryType->getName() === "checkout" && $lastTimeEntryOfEmployer->getTimeEntryType()->getName() !== "checkin") {
            throw new \Exception("Du musst dich zuerst einchecken Code:2");
        }

        //if checkin after checkin
        if ($this->timeEntryType->getName() === "checkin" && $lastTimeEntryOfEmployer->getTimeEntryType()->getName() === "checkin") {

            $appSettings = $this->companyAppSettingsRepository->findOneBy(['company'=>$this->companyService->getCurrentCompany()]);

            //check how much time passed since last checkin
            $lastCheckinOfEmployer = $lastTimeEntryOfEmployer->getCreatedAt();

            $hoursSinceLastCheckin = $this->timeDiffInMinutes($this->date->getTimestamp(), $lastCheckinOfEmployer->getTimestamp()) / 60;

            if($this->appSettings->getAutoCheckoutAfterHours() <= $hoursSinceLastCheckin){

                $autoHours = $appSettings->getAutoCheckoutGiveHours() * 60 * 60;
                $newCheckoutTime = $lastCheckinOfEmployer->getTimestamp() + $autoHours;
                $checkOutDateTime = date('Y-m-d H:i:s',$newCheckoutTime);
                $checkOutDateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s',$checkOutDateTime);

                $this->userCheckAction($this->employerId,"checkout",$checkOutDateTime);
            }
            throw new \Exception("Du bist schon eingecheckt.");
        }

        if ($this->timeEntryType->getName() === "checkout" && $lastTimeEntryOfEmployer->getTimeEntryType()->getName() === "checkin") {
            return true;
        }

        if ($this->timeEntryType->getName() === "pause" && $lastTimeEntryOfEmployer->getTimeEntryType()->getName() === "checkin") {
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

    private function getLastCheckinOfEmployer($employer, $type = "checkin"): ?DateTimeImmutable
    {
        $checkInType = $this->timeEntryTypeRepository->findOneBy(['name' => $type]);
        $lastCheckInOfUser = $this->timeEntryRepository->findOneBy(['timeEntryType' => $checkInType, 'employer' => $employer], ['createdAt' => 'desc']);

        if ($lastCheckInOfUser === null) {
            return null;
        } else {
            return $lastCheckInOfUser->getCreatedAt();
        }

    }

    public function timeDiffInMinutes($date1, $date2): float|int
    {
        return abs($date1 - $date2) / 60;
    }

}