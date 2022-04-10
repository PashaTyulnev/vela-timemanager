<?php

namespace App\Service;

use App\Repository\CompanyObjectRepository;

class WorktimeService
{
    private CompanyObjectRepository $companyObjectRepository;
    private CompanyService $companyService;

    public function __construct(CompanyObjectRepository $companyObjectRepository, CompanyService $companyService)
    {

        $this->companyObjectRepository = $companyObjectRepository;
        $this->companyService = $companyService;
    }

    public function getWorkTimeOfObject($objectId){
        //check if object belongs to admin company
        $object = $this->companyObjectRepository->findOneBy(['id'=>$objectId]);
        $objectCompany = $object->getCompany();
        $adminCompany = $this->companyService->getCurrentCompany();

        //get all time entries of company
        if($objectCompany === $adminCompany){

            return $this->formatTimeEntries($object->getTimeEntries());
        }
        else{
            throw new \Exception("Keine Rechte dieses Objekt einzusehen");
        }

    }

    private function formatTimeEntries($timeEntries){
        $formatArray = [];

        $employerBuffer = [];
        $timeEntryIndex = 0;
        $bufferCounter = 0;
        foreach ($timeEntries as $timeEntry){

            $employer = $timeEntry->getEmployer();
            $employerId = $employer->getId();
            $employerFirstName = $employer->getFirstName();
            $employerLastName = $employer->getLastName();
            $timeEntryDateTime = $timeEntry->getCreatedAt();
            $timeEntryType = $timeEntry->getTimeEntryType();
            $autoCheckout = $timeEntry->getAutoCheckOut();

            if($timeEntryType->getName() === "checkin"){

                $formatArray[$timeEntryIndex]['name'] =$employerFirstName . " " . $employerLastName;
                $formatArray[$timeEntryIndex]['start'] = $timeEntryDateTime;
                $formatArray[$timeEntryIndex]['autoCheckout'] = false;
                $employerBuffer[$bufferCounter][$employer->getId()]= $timeEntryIndex;
                $timeEntryIndex++;
                //buffer knows now, that employer X has checked id and stored his time entry index

            }elseif ($timeEntryType->getName() === "checkout"){
               foreach ($employerBuffer as $index => $bufferItem){
                   if($employerId === key($bufferItem)){
                       $formatArray[$bufferItem[$employerId]]['end'] = $timeEntryDateTime;


                       $timeDifference = abs(  $formatArray[$bufferItem[$employerId]]['start']->getTimestamp() -  $formatArray[$bufferItem[$employerId]]['end']->getTimestamp()) / 60;

                       $formatArray[$bufferItem[$employerId]]['start'] = $formatArray[$bufferItem[$employerId]]['start'];
                       $formatArray[$bufferItem[$employerId]]['end'] = $formatArray[$bufferItem[$employerId]]['end'];
                       $formatArray[$bufferItem[$employerId]]['sum'] = date('H:i', $timeDifference*60) ;

                       if($autoCheckout !== null){
                           $formatArray[$timeEntryIndex]['autoCheckout'] = true;
                       }
                       unset($employerBuffer[$index]);
                   }
               }
            }

        }
        return $formatArray;
    }

}