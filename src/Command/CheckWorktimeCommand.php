<?php

namespace App\Command;

use App\Entity\TimeEntry;
use App\Repository\CompanyAppSettingsRepository;
use App\Repository\CompanyUserRepository;
use App\Repository\TimeEntryRepository;
use App\Service\EmployerService;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'worktime:check',
    description: 'Check work-times of Employers',
)]
class CheckWorktimeCommand extends Command
{


    private TimeEntryRepository $timeEntryRepository;
    private CompanyAppSettingsRepository $companyAppSettingsRepository;
    private CompanyUserRepository $companyUserRepository;
    private EmployerService $employerService;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(TimeEntryRepository          $timeEntryRepository,
                                CompanyAppSettingsRepository $companyAppSettingsRepository,
                                CompanyUserRepository        $companyUserRepository,
                                EmployerService              $employerService,
                                EntityManagerInterface       $entityManager,
                                LoggerInterface              $logger)
    {
        parent::__construct();
        $this->timeEntryRepository = $timeEntryRepository;
        $this->companyAppSettingsRepository = $companyAppSettingsRepository;
        $this->companyUserRepository = $companyUserRepository;
        $this->employerService = $employerService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $allUsers = $this->companyUserRepository->findAll();

        $this->logger->info("CHECKOUT CHECKER STARTED AT: " . date('d.m.Y H:i:s') . '--SERVERTIME');
        foreach ($allUsers as $employer) {
            $lastMainCheckin = $this->employerService->getLastMainCheckin($employer);
            $lastTimeEntry = $this->employerService->getLastTimeEntryOfEmployer($employer);

            $io->comment("-------------------");
            $io->comment("CHECK USER ". $employer->getFirstName() . " ". $employer->getLastName());

            if ($lastMainCheckin != null) {

                if ($lastTimeEntry->getTimeEntryType()->getName() !== 'checkout') {

                    $tz = new DateTimeZone("Europe/Berlin");
                    $timeNow = new DateTime($this->employerService->getNow()->format("Y-m-d H:i:s"), $tz);
                    $timeLastCheckin = new DateTime($lastMainCheckin->getCreatedAt()->format("Y-m-d H:i:s"), $tz);

                    $company = $employer->getCompany();
                    $companySettings = $this->companyAppSettingsRepository->findOneBy(['company' => $company]);
                    $minutesInWorkday = $companySettings->getHoursBetweenShifts() * 60;
                    //Time since unchecked out time entry
                    $timeDiffMinutes = ($timeNow->getTimestamp() - $timeLastCheckin->getTimestamp())/60;
                    $autoCheckoutGiveMinutes = $companySettings->getAutoCheckoutGiveHours() * 60;
                    $object = $lastTimeEntry->getObject();
                    $checkOutTypeObject = $this->employerService->getTimeEntryTypeByName('checkout');


                    if ($timeDiffMinutes >= $minutesInWorkday) {

                        $newCheckoutTime = ($timeLastCheckin->getTimestamp()+7200) + $autoCheckoutGiveMinutes * 60;

                        $newDateTime = (new \DateTimeImmutable())->setTimestamp($newCheckoutTime);

                        $io->comment($newDateTime->format("d.m.Y H:i:s"));

                        $newDateTime->setTimestamp($newCheckoutTime);
                        $timeEntry = new TimeEntry();
                        $timeEntry->setTimeEntryType($checkOutTypeObject);
                        $timeEntry->setEmployer($employer);
                        $timeEntry->setCreatedAt($newDateTime);
                        $timeEntry->setAutoCheckOut(true);
                        $timeEntry->setObject($object);
                        $timeEntry->setMainCheckin(false);

                        $this->entityManager->persist($timeEntry);
                        $this->entityManager->flush();

                        $this->logger->info("CHECKOUT für User". $employer->getFirstName() . " ". $employer->getLastName(). " erstellt.");

                    }
                }
            }
        }


        $io->success('Command erfolgreich beendet.');

        return Command::SUCCESS;
    }
}
