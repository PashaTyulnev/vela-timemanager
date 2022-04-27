<?php

namespace App\Service;

use App\Repository\CompanyObjectRepository;
use App\Repository\TimeEntryRepository;
use DateInterval;
use DateTimeImmutable;
use Dompdf\Dompdf;

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
     * @throws \Exception
     */
    public function getWorkTimeOfObject($objectId, $month=null, $year=null)
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

            return $this->formatTimeEntries($this->timeEntryRepository->findByDate($objectId,$firstDayInMonth,$lastDayInMonth));
        } else {
            throw new \Exception("Keine Rechte dieses Objekt einzusehen");
        }

    }

    private function formatTimeEntries($timeEntries)
    {
        $formatArray = [];

        $employerBuffer = [];
        $timeEntryIndex = 0;
        $bufferCounter = 0;
        foreach ($timeEntries as $timeEntry) {

            $employer = $timeEntry->getEmployer();
            $employerId = $employer->getId();
            $employerFirstName = $employer->getFirstName();
            $employerLastName = $employer->getLastName();
            $timeEntryDateTime = $timeEntry->getCreatedAt();
            $timeEntryType = $timeEntry->getTimeEntryType();
            $autoCheckout = $timeEntry->getAutoCheckOut();

            if ($timeEntryType->getName() === "checkin") {

                $formatArray[$timeEntryIndex]['name'] = $employerFirstName . " " . $employerLastName;
                $formatArray[$timeEntryIndex]['start'] = $timeEntryDateTime;
                $formatArray[$timeEntryIndex]['autoCheckout'] = false;
                $employerBuffer[$bufferCounter][$employer->getId()] = $timeEntryIndex;
                $timeEntryIndex++;
                //buffer knows now, that employer X has checked id and stored his time entry index

            } elseif ($timeEntryType->getName() === "checkout") {
                foreach ($employerBuffer as $index => $bufferItem) {
                    if ($employerId === key($bufferItem)) {
                        $formatArray[$bufferItem[$employerId]]['end'] = $timeEntryDateTime;

                        //total time
                        $timeDifference = $formatArray[$bufferItem[$employerId]]['start']->diff($formatArray[$bufferItem[$employerId]]['end']);

                        $totalHours = $this->getTotalHours($timeDifference);
                        $fullHours = floor($totalHours);

                        $totalMinutes = $this->getTotalMinutes($timeDifference);
                        $leftMinutes = $totalMinutes - $fullHours * 60;
                        if (strlen($leftMinutes) === 1) {
                            $leftMinutes = "0" . $leftMinutes;
                        }
                        $formatArray[$bufferItem[$employerId]]['sum'] = $fullHours . ":" . $leftMinutes;
//
//                       if($autoCheckout === true){
//                           $formatArray[$timeEntryIndex]['autoCheckout'] = true;
//                       }
                        unset($employerBuffer[$index]);
                    }
                }
            }

        }
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

}