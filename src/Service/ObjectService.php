<?php

namespace App\Service;

use App\Entity\CompanyMainUser;
use App\Entity\CompanyObject;
use App\Repository\CompanyAppSettingsRepository;
use App\Repository\CompanyObjectRepository;
use App\Repository\CompanyUserRepository;
use App\Repository\TimeEntryRepository;
use App\Repository\TimeEntryTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;

class ObjectService
{
    private CompanyUserRepository $employerRepository;
    private TimeEntryTypeRepository $timeEntryTypeRepository;
    private EntityManagerInterface $entityManager;
    private TimeEntryRepository $timeEntryRepository;
    private CompanyService $companyService;
    private CompanyAppSettingsRepository $companyAppSettingsRepository;
    private CompanyObjectRepository $objectRepository;

    public function __construct(CompanyUserRepository        $employerRepository,
                                TimeEntryTypeRepository      $timeEntryTypeRepository,
                                EntityManagerInterface       $entityManager,
                                TimeEntryRepository          $timeEntryRepository,
                                CompanyService               $companyService,
                                CompanyAppSettingsRepository $companyAppSettingsRepository,
    CompanyObjectRepository $objectRepository
    )
    {

        $this->employerRepository = $employerRepository;
        $this->timeEntryTypeRepository = $timeEntryTypeRepository;
        $this->entityManager = $entityManager;
        $this->timeEntryRepository = $timeEntryRepository;
        $this->companyService = $companyService;
        $this->companyAppSettingsRepository = $companyAppSettingsRepository;
        $this->objectRepository = $objectRepository;
    }

    #[ArrayShape(['success' => "string"])] public function createNewObject($request)
    {
        $currentCompany = $this->companyService->getCurrentCompany();

        $newObject = new CompanyObject();

        if(!$request->get('street')){
            throw new \Exception("Bitte geben Sie einen Straßennamen ein!");
        }
        if(!$request->get('number')){
            throw new \Exception("Bitte geben Sie einen Hausnummer ein!");
        }

        $street = $request->get('street');
        $number = $request->get('number');


        $newObject->setStreet($street);
        $newObject->setNumber($number);
        $newObject->setCompany($currentCompany);

        $this->entityManager->persist($newObject);
        $this->entityManager->flush();

        //neuer username anhand des objektnamens

        $streetString = preg_replace("/[^a-zA-Z0-9]+/", "", $street);
        preg_match_all('!\d+\.*\d*!', $number, $numberString);


        $streetShort = $streetString[0].$streetString[1].$streetString[2].$numberString[0][0];
        $streetShort = strtolower($streetShort);


        $companyShort = $currentCompany->getCode();

        //object user erstellen
        $newObjectUser = new CompanyMainUser();
        $newObjectUser->setCompany(null);
        $newObjectUser->setCompanyObject($newObject);
        $newObjectUser->setEmail($streetShort."@".$companyShort.".de");
        $newObjectUser->setPassword('');
        $newObjectUser->setRoles(json_encode(["ROLE_MAIN"]));

        $this->entityManager->persist($newObjectUser);
        $this->entityManager->flush();

        return [
            'success' => "Objekt wurde erstellt!"
        ];
    }

    public function getAllObjects(): array
    {
        $currentCompany = $this->companyService->getCurrentCompany();
        return $this->objectRepository->findBy(['company'=>$currentCompany]);

    }
}