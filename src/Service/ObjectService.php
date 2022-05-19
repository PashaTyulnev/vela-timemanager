<?php

namespace App\Service;

use App\Entity\CompanyMainUser;
use App\Entity\CompanyObject;
use App\Repository\CompanyAppSettingsRepository;
use App\Repository\CompanyMainUserRepository;
use App\Repository\CompanyObjectRepository;
use App\Repository\CompanyUserRepository;
use App\Repository\TimeEntryRepository;
use App\Repository\TimeEntryTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ObjectService
{
    private CompanyUserRepository $employerRepository;
    private TimeEntryTypeRepository $timeEntryTypeRepository;
    private EntityManagerInterface $entityManager;
    private TimeEntryRepository $timeEntryRepository;
    private CompanyService $companyService;
    private CompanyAppSettingsRepository $companyAppSettingsRepository;
    private CompanyObjectRepository $objectRepository;
    private CompanyMainUserRepository $mainUserRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(CompanyUserRepository        $employerRepository,
                                TimeEntryTypeRepository      $timeEntryTypeRepository,
                                EntityManagerInterface       $entityManager,
                                TimeEntryRepository          $timeEntryRepository,
                                CompanyService               $companyService,
                                CompanyAppSettingsRepository $companyAppSettingsRepository,
                                CompanyMainUserRepository    $mainUserRepository,
                                CompanyObjectRepository      $objectRepository,
                                UserPasswordHasherInterface  $passwordHasher
    )
    {

        $this->employerRepository = $employerRepository;
        $this->timeEntryTypeRepository = $timeEntryTypeRepository;
        $this->entityManager = $entityManager;
        $this->timeEntryRepository = $timeEntryRepository;
        $this->companyService = $companyService;
        $this->companyAppSettingsRepository = $companyAppSettingsRepository;
        $this->objectRepository = $objectRepository;
        $this->mainUserRepository = $mainUserRepository;
        $this->passwordHasher = $passwordHasher;
    }

    #[ArrayShape(['success' => "string"])]
    public function createNewObject($request): array
    {

        $currentCompany = $this->companyService->getCurrentCompany();

        $newObject = new CompanyObject();

        if (!$request->get('street')) {
            throw new \Exception("Bitte geben Sie einen Straßennamen ein!");
        }
        if (!$request->get('number')) {
            throw new \Exception("Bitte geben Sie einen Hausnummer ein!");
        }

        $street = $request->get('street');
        $number = $request->get('number');

        if (preg_match('~[0-9]+~', $street)) {
            throw new \Exception("Bitte geben Sie nur den Straßennamen / Platz in das erste Feld ein.");
        }

        //neuer username anhand des objektnamens

        $streetString = preg_replace("/[^a-zA-Z0-9]+/", "", $street);
        preg_match_all('!\d+\.*\d*!', $number, $numberString);


        $streetShort = $streetString[0] . $streetString[1] . $streetString[2] . $numberString[0][0];
        $streetShort = strtolower($streetShort);

        $companyShort = $currentCompany->getCode();

        $plaintextPassword = $streetShort."$".$companyShort;

        $alreadyExists = $this->objectRepository->findOneBy(['company'=>$currentCompany,'shortName'=>$streetShort]);

        if($alreadyExists != null){
            throw  new \Exception("Objekt ist bereits angelegt / existiert schon!");
        }
        $newObject->setStreet($street);
        $newObject->setShortName($streetShort);
        $newObject->setNumber($number);
        $newObject->setCompany($currentCompany);
        $newObject->setPassword($plaintextPassword);

        $this->entityManager->persist($newObject);
        $this->entityManager->flush();

        //object user erstellen
        $newObjectUser = new CompanyMainUser();

        //passwort muss was simples und schweres für Bots sein
        $hashedPassword = $this->passwordHasher->hashPassword(
            $newObjectUser,
            $plaintextPassword
        );


        $newObjectUser->setCompany(null);
        $newObjectUser->setCompanyObject($newObject);
        $newObjectUser->setEmail($streetShort . "@" . $companyShort . ".de");
        $newObjectUser->setPassword($hashedPassword);
        $newObjectUser->setRoles(['ROLE_MAIN']);

        $this->entityManager->persist($newObjectUser);
        $this->entityManager->flush();

        return [
            'success' => "Objekt wurde erstellt!"
        ];
    }

    public function getAllObjects(): array
    {
        $currentCompany = $this->companyService->getCurrentCompany();
        return $this->objectRepository->findBy(['company' => $currentCompany]);

    }
}