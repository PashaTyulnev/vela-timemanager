<?php

namespace App\Service;

use App\Repository\CompanyObjectRepository;
use App\Repository\TimeEntryRepository;
use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Exception;

class WorktimeService
{
    private CompanyObjectRepository $companyObjectRepository;
    private CompanyService $companyService;
    private TimeEntryRepository $timeEntryRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(CompanyObjectRepository $companyObjectRepository, CompanyService $companyService, TimeEntryRepository $timeEntryRepository, EntityManagerInterface $entityManager)
    {

        $this->companyObjectRepository = $companyObjectRepository;
        $this->companyService = $companyService;
        $this->timeEntryRepository = $timeEntryRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @throws Exception
     */
    public function getWorkTimeOfObject($objectId, $month = null, $year = null, $employer = null): array
    {
        //wenn kein Monat gegeben ist, lade Daten von aktuellem Monat
        if ($month + $year == null) {
            $month = date('m');
            $year = date('Y');
        }


        $firstDayInMonth = new DateTimeImmutable($year . '-' . $month . '-1');
        $lastDayInMonth = new DateTimeImmutable($year . '-' . $month . '-1');

        $lastDayInMonth = $lastDayInMonth->format('Y-m-t');

        //check if object belongs to admin company
        $object = $this->companyObjectRepository->findOneBy(['id' => $objectId]);

        $objectCompany = $object->getCompany();
        $adminCompany = $this->companyService->getCurrentCompany();


        //get all time entries of company
        if ($objectCompany === $adminCompany) {
            return $this->formatTimeEntries($this->timeEntryRepository->findByDate($objectId, $firstDayInMonth, $lastDayInMonth, $employer));
        } else {
            throw new Exception("Keine Rechte dieses Objekt einzusehen");
        }

    }

    private function formatTimeEntries($timeEntries): array
    {
        $formatArray = [];

        $totalFinalHours = 0;
        $totalFinalMinutes = 0;
        foreach ($timeEntries as $timeEntry) {

            $employer = $timeEntry->getEmployer();
            $employerId = $employer->getId();
            $employerFirstName = $employer->getFirstName();
            $employerLastName = $employer->getLastName();
            $timeEntryDateTime = $timeEntry->getCreatedAt();
            $timeEntryType = $timeEntry->getTimeEntryType();
            $autoCheckout = $timeEntry->getAutoCheckOut();
            $uid = $timeEntry->getUid();
            $removed = $timeEntry->getRemoved();

            if ($uid != null && !$removed) {
                //wenn der Eintrag ein checkin ist, dann wissen wir den Startpunkt
                if ($timeEntryType->getName() === "checkin") {
                    $formatArray['worktimes'][$uid]['name'] = $employerFirstName . " " . $employerLastName;
                    $formatArray['worktimes'][$uid]['start'] = $timeEntryDateTime;
                    $formatArray['worktimes'][$uid]['autoCheckout'] = false;

                } elseif ($timeEntryType->getName() === "checkout") {
                    $formatArray['worktimes'][$uid]['end'] = $timeEntryDateTime;
                }

            }
        }

        $arrayWithTotalForEachEmployer = $this->calculateSumForEveryWorker($formatArray);

        return $this->calculateSumForWholeMonth($arrayWithTotalForEachEmployer);
    }

    public function calculateSumForWholeMonth($formattedArray): array
    {
        $totalHours = 0;
        $totalMinutes = 0;
        foreach ($formattedArray['worktimes'] as $index => $workTime){
            if(isset($workTime['sum'])){
                $time = explode(":", $workTime['sum']);
                $hours = intval($time[0]);
                $minutes = intval($time[1]);

                $totalHours += $hours;
                $totalMinutes += $minutes;

            }
        }

        $totalMinutesLeft = $totalMinutes / 60;
        $hoursFromMinutes = floor($totalMinutesLeft);
        $totalHours += $hoursFromMinutes;
        $totalMinutes -= floor($totalMinutesLeft)*60;


        $formattedArray['totalHours'] = $totalHours . ":" . $totalMinutes;
        return $formattedArray;
    }

    public function calculateSumForEveryWorker($formattedArray): array
    {

        foreach ($formattedArray['worktimes'] as $index => $workTime){

            if(isset($workTime['end'])){
                $timeDifference = $workTime['start']->diff($workTime['end']);

                $totalHours = $this->getTotalHours($timeDifference);
                $fullHours = floor($totalHours);

                $totalMinutes = $this->getTotalMinutes($timeDifference);
                $leftMinutes = $totalMinutes - $fullHours * 60;
                if (strlen($leftMinutes) === 1) {
                    $leftMinutes = "0" . $leftMinutes;
                }
                $formattedArray['worktimes'][$index]['sum'] = $fullHours . ":" . $leftMinutes;
            }
        }
        return $formattedArray;
    }

    public function getTotalMinutes(DateInterval $int): float|int
    {
        return ($int->d * 24 * 60) + ($int->h * 60) + $int->i;
    }

    public function getTotalHours(DateInterval $int): float|int
    {
        return ($int->d * 24) + $int->h + $int->i / 60;
    }

    public function getWorktimeDataForPdf()
    {

        // instantiate and use the dompdf class
        $dompdf = new Dompdf();
        $dompdf->loadHtml($this->renderView('index.html', array('name' => 'Fabien')));

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream();

    }


    /**
     * @throws Exception
     */
    public function getMonthsForSelect($object)
    {
        // monate für frontend dropdown
        $months = [];

        $minDate = $this->timeEntryRepository->findMinDate($object);
        $minDate = new DateTime($minDate[0][1]);
        $firstDay = $minDate->format('Y-m-1');

        $maxDate = $this->timeEntryRepository->findMaxDate($object);
        $maxDate = new DateTime($maxDate[0][1]);
        $lastDay = $maxDate->format('Y-m-t');

        // jetzt kann man iterieren

        $begin = new DateTime($firstDay);
        $end = new DateTime($lastDay);

        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($begin, $interval, $end);
        $i = 0;
        foreach ($period as $dt) {
            $month = $dt->format('m');
            $year = $dt->format('Y');

            if ($month === '01') {
                $mName = "Januar";
            }
            if ($month === '02') {
                $mName = "Februar";
            }
            if ($month === '03') {
                $mName = "März";
            }
            if ($month === '04') {
                $mName = "April";
            }
            if ($month === '05') {
                $mName = "Mai";
            }
            if ($month === '06') {
                $mName = "Juni";
            }
            if ($month === '07') {
                $mName = "Juli";
            }
            if ($month === '08') {
                $mName = "August";
            }
            if ($month === '09') {
                $mName = "September";
            }
            if ($month === '10') {
                $mName = "Oktober";
            }
            if ($month === '11') {
                $mName = "November";
            }
            if ($month === '12') {
                $mName = "Dezember";
            }

            $months[$i]['month'] = $mName;
            $months[$i]['monthNumber'] = (int)$month;
            $months[$i]['year'] = $year;

            $i++;
        }

        return $months;
    }

    public function getTimeEntryGroup($uid): array
    {

        return $this->formatTimeEntryGroup($this->timeEntryRepository->findBy(['uid'=>$uid]));

    }

    public function formatTimeEntryGroup($timeEntries){
        $array = [];

        foreach ($timeEntries as $index => $timeEntry){

            $employer = $timeEntry->getEmployer();
            $timeEntryDateTime = $timeEntry->getCreatedAt();
            $timeEntryType = $timeEntry->getTimeEntryType();

            $array[$index]['type'] = $timeEntryType->getName();
            $array[$index]['time'] = $timeEntryDateTime;
            $array[$index]['id'] = $timeEntry->getId();
        }

        return $array;
    }

    public function getEmployerByUid($uid): ?\App\Entity\CompanyUser
    {
        return $this->timeEntryRepository->findBy(['uid'=>$uid])[0]->getEmployer();
    }

    /**
     * @throws Exception
     */
    public function saveTimeEntryChange(array $allTimeEntriesData)
    {
        $idsToChange = [];

        foreach ($allTimeEntriesData as $index => $date){
            $mix = explode("-", $index);
            $id = $mix[1];
            $idsToChange[] = $id;

//            $realTimeEntry = $this->timeEntryRepository->findOneBy(['id'=>$id]);
//
//            if($type == "date"){
//              $date =  DateTimeImmutable::createFromFormat('Y-m-d', $date)->format('Y-m-d');
//
//            }

//            $this->entityManager->persist($timeEntry);
//            $this->entityManager->flush();

        }


        $idsToChange = array_unique($idsToChange);

        foreach ($idsToChange as $id){
            $newDate = $allTimeEntriesData['date-'.$id];
            $newTime = $allTimeEntriesData['time-'.$id];
            $realTimeEntry = $this->timeEntryRepository->findOneBy(['id'=>$id]);

            $dateToSave =  new DateTimeImmutable($newDate. " " . $newTime);

            $realTimeEntry->setCreatedAt($dateToSave);

            $this->entityManager->persist($realTimeEntry);
            $this->entityManager->flush();


        }
    }

    public function deleteTimeEntries($uid): bool
    {
        $timeEntries = $this->timeEntryRepository->findBy(['uid'=> $uid]);

        foreach ($timeEntries as $timeEntry){
            $timeEntry->setRemoved(true);
            $this->entityManager->persist($timeEntry);
            $this->entityManager->flush();
        }

        return true;

    }

}