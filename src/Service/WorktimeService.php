<?php

namespace App\Service;

use App\Repository\CompanyObjectRepository;
use App\Repository\TimeEntryRepository;
use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use Dompdf\Dompdf;
use Exception;

class WorktimeService
{
    private CompanyObjectRepository $companyObjectRepository;
    private CompanyService $companyService;
    private TimeEntryRepository $timeEntryRepository;

    public function __construct(CompanyObjectRepository $companyObjectRepository, CompanyService $companyService, TimeEntryRepository $timeEntryRepository)
    {

        $this->companyObjectRepository = $companyObjectRepository;
        $this->companyService = $companyService;
        $this->timeEntryRepository = $timeEntryRepository;
    }

    /**
     * @throws Exception
     */
    public function getWorkTimeOfObject($objectId, $month=null, $year=null,$employer = null): array
    {
        //wenn kein Monat gegeben ist, lade Daten von aktuellem Monat
        if($month + $year == null){
            $month = date('m');
            $year = date('Y');
        }


        $firstDayInMonth = new DateTimeImmutable($year.'-'.$month.'-1');
        $lastDayInMonth =  new DateTimeImmutable($year.'-'.$month.'-1');

        $lastDayInMonth = $lastDayInMonth->format('Y-m-t');

        //check if object belongs to admin company
        $object = $this->companyObjectRepository->findOneBy(['id' => $objectId]);
        $objectCompany = $object->getCompany();
        $adminCompany = $this->companyService->getCurrentCompany();


        //get all time entries of company
        if ($objectCompany === $adminCompany) {

            return $this->formatTimeEntries($this->timeEntryRepository->findByDate($objectId,$firstDayInMonth,$lastDayInMonth,$employer));
        } else {
            throw new Exception("Keine Rechte dieses Objekt einzusehen");
        }

    }

    private function formatTimeEntries($timeEntries)
    {
        $formatArray = [];
        $employerBuffer = [];
        $timeEntryIndex = 0;
        $bufferCounter = 0;
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

            if ($timeEntryType->getName() === "checkin") {

                $formatArray['worktimes'][$timeEntryIndex]['name'] = $employerFirstName . " " . $employerLastName;
                $formatArray['worktimes'][$timeEntryIndex]['start'] = $timeEntryDateTime;
                $formatArray['worktimes'][$timeEntryIndex]['autoCheckout'] = false;
                $employerBuffer[$bufferCounter][$employer->getId()] = $timeEntryIndex;
                $timeEntryIndex++;
                //buffer knows now, that employer X has checked id and stored his time entry index

            } elseif ($timeEntryType->getName() === "checkout") {
                foreach ($employerBuffer as $index => $bufferItem) {
                    if ($employerId === key($bufferItem)) {
                        $formatArray['worktimes'][$bufferItem[$employerId]]['end'] = $timeEntryDateTime;

                        //total time
                        $timeDifference = $formatArray['worktimes'][$bufferItem[$employerId]]['start']->diff($formatArray['worktimes'][$bufferItem[$employerId]]['end']);

                        $totalHours = $this->getTotalHours($timeDifference);
                        $fullHours = floor($totalHours);

                        $totalMinutes = $this->getTotalMinutes($timeDifference);
                        $leftMinutes = $totalMinutes - $fullHours * 60;
                        if (strlen($leftMinutes) === 1) {
                            $leftMinutes = "0" . $leftMinutes;
                        }
                        $formatArray['worktimes'][$bufferItem[$employerId]]['sum'] = $fullHours . ":" . $leftMinutes;
                      $totalFinalHours = $totalFinalHours + $fullHours;

                      $totalFinalMinutes = $totalFinalMinutes + $leftMinutes;
//                       if($autoCheckout === true){
//                           $formatArray[$timeEntryIndex]['autoCheckout'] = true;
//                       }
                        unset($employerBuffer[$index]);
                    }
                }
            }

        }

        //z.B. 110min sind 1h und 50min -> 1h
        $hoursFromMinutes = floor($totalFinalMinutes / 60);

        $minutesFromMinutes = $totalFinalMinutes % 60;

        if (strlen($minutesFromMinutes) === 1) {
            $minutesFromMinutes = "0" . $minutesFromMinutes;
        }

        $formatArray['totalHours'] = $totalFinalHours+$hoursFromMinutes . ":".$minutesFromMinutes;
        return $formatArray;
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

            if($month === '01'){
                $mName = "Januar";
            }
            if($month === '02'){
                $mName = "Februar";
            }
            if($month === '03'){
                $mName = "März";
            }
            if($month === '04'){
                $mName = "April";
            }
            if($month === '05'){
                $mName = "Mai";
            }
            if($month === '06'){
                $mName = "Juni";
            }
            if($month === '07'){
                $mName = "Juli";
            }
            if($month === '08'){
                $mName = "August";
            }
            if($month === '09'){
                $mName = "September";
            }
            if($month === '10'){
                $mName = "Oktober";
            }
            if($month === '11'){
                $mName = "November";
            }
            if($month === '12'){
                $mName = "Dezember";
            }

            $months[$i]['month'] = $mName;
            $months[$i]['monthNumber'] = (int) $month;
            $months[$i]['year'] = $year;

            $i++;
        }

        return $months;
    }

}