<?php

namespace App\Service;

use App\Entity\CompanyAppSettings;
use App\Entity\CompanyUser;
use App\Entity\TimeEntry;
use App\Repository\CompanyAppSettingsRepository;
use App\Repository\CompanyUserRepository;
use App\Repository\TimeEntryRepository;
use App\Repository\TimeEntryTypeRepository;
use DateTimeImmutable;
use DateTimeZone;
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


    /**
     * @throws \Exception
     */
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
        $this->date = new DateTimeImmutable('', new DateTimeZone('Europe/Berlin'));
        $this->companyAppSettingsRepository = $companyAppSettingsRepository;

    }

    public function getNow(): DateTimeImmutable
    {
        return $this->date;
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
     * @param $id
     * @param $checkInType
     * @return TimeEntry
     *
     * Check in, check out oder Pause
     * @throws \Exception
     */
    public function userCheckAction($id, $checkInType, $autoCheckoutTime = 0): TimeEntry
    {

        //timeEntryType Object
        $this->timeEntryType = $this->getTimeEntryTypeByName($checkInType);

        $timeEntry = new TimeEntry();

        $this->employerId = $id;

        //if not an auto-checkout
        if ($autoCheckoutTime === 0) {
            //check if this action is valid
            $isValid = $this->checkIfActionAllowed();
        }

        $timeEntry->setMainCheckin(false);

        if (isset($isValid) && $isValid === "mainCheckin") {
            $timeEntry->setMainCheckin(true);
        }
        $timeEntry->setEmployer($this->getEmployerById($id));
        $timeEntry->setTimeEntryType($this->timeEntryType);

        if ($autoCheckoutTime !== 0) {
            $timeEntry->setCreatedAt($autoCheckoutTime);
        } else {
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
    private function checkIfActionAllowed(): bool|string
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

        $lastTimeEntryOfEmployer = $this->getLastTimeEntryOfEmployer($this->employerId);


        //if no entry yet and checkin action then valid
        if ($lastTimeEntryOfEmployerSameType === null && $this->timeEntryType->getName() === "checkin") {
            return true;
        }

        //if just checked in
        $lastCheckInTime = $this->getLastTimeEntryOfEmployer($this->employerId, 'checkin')->getCreatedAt();
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

        //if checkout was on same work day
        if (
            $this->timeEntryType->getName() === "checkin" &&
            $lastTimeEntryOfEmployer->getTimeEntryType()->getName() === "checkout") {
            $minutesBetweenShifts = $this->companyAppSettingsRepository->findOneBy(['company' => $this->companyService->getCurrentCompany()])->getHoursBetweenShifts() * 60;
            if (($this->timeDiffInMinutes($this->date->getTimestamp(), $lastTimeEntryOfEmployer->getCreatedAt()->getTimestamp()) < $minutesBetweenShifts)) {
                $checkinPossible = $lastTimeEntryOfEmployer->getCreatedAt()->add(new \DateInterval('PT' . $minutesBetweenShifts . "M"));
                throw new \Exception("Du hast Feierabend und kannst dich erst wieder ab" . $checkinPossible->format("d.m.Y h:i") . " einchecken.");
            } else {
                return "mainCheckin";
            }

        }

        // ($this->timeDiffInMinutes($this->date->getTimestamp(), $lastCheckInTime->getTimestamp())

        //if checkin after checkin
        if ($this->timeEntryType->getName() === "checkin" && $lastTimeEntryOfEmployer->getTimeEntryType()->getName() === "checkin") {

            $appSettings = $this->companyAppSettingsRepository->findOneBy(['company' => $this->companyService->getCurrentCompany()]);


            //check how much time passed since last checkin
            $lastCheckinOfEmployer = $lastTimeEntryOfEmployer->getCreatedAt();

            $hoursSinceLastCheckin = $this->timeDiffInMinutes($this->date->getTimestamp(), $lastCheckinOfEmployer->getTimestamp()) / 60;

            if ($appSettings->getAutoCheckoutAfterHours() <= $hoursSinceLastCheckin) {

                $autoHours = $appSettings->getAutoCheckoutGiveHours() * 60 * 60;
                $newCheckoutTime = $lastCheckinOfEmployer->getTimestamp() + $autoHours;
                $checkOutDateTime = date('Y-m-d H:i:s', $newCheckoutTime);
                $checkOutDateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $checkOutDateTime);

                $this->userCheckAction($this->employerId, "checkout", $checkOutDateTime);
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

    public function getLastTimeEntryOfEmployer($employer, $type = null): ?TimeEntry
    {
        //if search after specific status
        if ($type !== null) {
            $checkInType = $this->timeEntryTypeRepository->findOneBy(['name' => $type]);
            return $this->timeEntryRepository->findOneBy(['timeEntryType' => $checkInType, 'employer' => $employer], ['createdAt' => 'desc']);
        } else {
            return $this->timeEntryRepository->findOneBy(['employer' => $employer], ['createdAt' => 'desc']);
        }

    }

    public function timeDiffInMinutes($date1, $date2): float|int
    {
        return abs($date1 - $date2) / 60;
    }

    public function getEmployerStatusString($employer): string
    {
        $lastTimeEntry = $this->getLastTimeEntryOfEmployer($employer);

        if($lastTimeEntry != null){
            if ($lastTimeEntry->getTimeEntryType()->getName() === "checkin") {
                $mainCheckin = $this->getLastMainCheckin($employer);
                if($mainCheckin != null){
                    return "Du arbeitest seit " . $mainCheckin->getCreatedAt()->format("d.m.Y H:i");
                }
                else{
                    return "Du arbeitest seit " . $lastTimeEntry->getCreatedAt()->format("d.m.Y H:i");
                }

            }
            if ($lastTimeEntry->getTimeEntryType()->getName() === "checkout") {
                return "Du hast Feierabend seit " . $lastTimeEntry->getCreatedAt()->format("d.m.Y H:i");
            }
            if ($lastTimeEntry->getTimeEntryType()->getName() === "pause") {
                //if pause, look what was before pause
                return "Du machst Pause seit " . $lastTimeEntry->getCreatedAt()->format("d.m.Y H:i");
            }
        }
        else {
            return "Das ist deine erste Benutzung dieser App. Bitte check dich ein.";
        }
        return "";
    }

    public function getLastMainCheckin($employer): ?TimeEntry
    {
        return $this->timeEntryRepository->findOneBy(['employer' => $employer, 'mainCheckin' => true], ['createdAt' => 'DESC']);
    }

    /**
     * @throws \Exception
     */
    public function createNewEmployer($request)
    {
        $currentCompany = $this->companyService->getCurrentCompany();
        $newEmployer = new CompanyUser();

        if(!$request->get('firstName')){
            throw new \Exception("Bitte geben Sie einen Vornamen ein!");
        }
        if(!$request->get('lastName')){
            throw new \Exception("Bitte geben Sie einen Nachnamen ein!");
        }

        $firstName = $request->get('firstName');
        $lastName = $request->get('lastName');

        //wenn pin automatisch generiert werden soll
        if(!$request->get('pin')){
           $pin = $this->createNewEmployerPin();
        }else{
            $pin = $request->get('pin');
            $pinExists = $this->checkSamePin($pin);

            if($pinExists){
                throw new \Exception("Person mit dieser Pin ist bereits angelegt!");
            }
        }

        $newEmployer->setCompany($currentCompany);
        $newEmployer->setFirstName($firstName);
        $newEmployer->setLastName($lastName);
        $newEmployer->setPin($pin);
        $this->entityManager->persist($newEmployer);
        $this->entityManager->flush();

        return [
            'success' => "Arbeiter wurde erstellt!"
        ];

    }

    public function createNewEmployerPin(): int
    {

        $currentCompany = $this->companyService->getCurrentCompany();

        // 4 stellig
        $pin = rand(1111,9888);

        $pinExists = $this->checkSamePin($pin);

        //wenn es niemanden mit dieser PIN in dieser Firma gibt, dann kann man die nehmen
        if(!$pinExists){
            return $pin;
        }else{
           $this->createNewEmployerPin();
        }
        return 0;
    }

    public function checkSamePin($pin): bool
    {
        $currentCompany = $this->companyService->getCurrentCompany();
        $pinExists = $this->employerRepository->findBy(['pin' => $pin,'company'=>$currentCompany]);

        if($pinExists === []){
            return false;
        }else{

          return true;
        }

    }

}