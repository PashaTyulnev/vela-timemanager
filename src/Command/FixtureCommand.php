<?php

namespace App\Command;

use App\Entity\TimeEntry;
use App\Repository\CompanyObjectRepository;
use App\Repository\CompanyUserRepository;
use App\Repository\TimeEntryTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'generate:data',
    description: 'Add a short description for your command',
)]
class FixtureCommand extends Command
{
    private TimeEntryTypeRepository $timeEntryTypeRepository;
    private CompanyUserRepository $companyUserRepository;
    private CompanyObjectRepository $companyObjectRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(

        TimeEntryTypeRepository $timeEntryTypeRepository,
        CompanyUserRepository $companyUserRepository,
        CompanyObjectRepository $companyObjectRepository,
        EntityManagerInterface       $entityManager){

        parent::__construct();
        $this->timeEntryTypeRepository = $timeEntryTypeRepository;
        $this->companyUserRepository = $companyUserRepository;
        $this->companyObjectRepository = $companyObjectRepository;
        $this->entityManager = $entityManager;
    }


    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $checkInType = $this->timeEntryTypeRepository->findOneBy(['name'=>'checkin']);
        $pauseType = $this->timeEntryTypeRepository->findOneBy(['name'=>'pause']);
        $checkoutType = $this->timeEntryTypeRepository->findOneBy(['name'=>'checkout']);

        $companyUsers = $this->companyUserRepository->findAll();

        $objects = $this->companyObjectRepository->findAll();

        $firstDate = new \DateTimeImmutable('2021-09-01 18:00');


        for ($i = 0; $i < 300; $i++) {

            $io->note($i*(100/300) . "%");

            $takePause = 0;

            $userRandom = random_int(0,2);

            $user = $companyUsers[$userRandom];

            $shiftHours = random_int(6,16);

            $pauseAfterHours = random_int(1,3);

            $pauseDuration = random_int(1,2);

            $shiftBegin = $firstDate;

            if($takePause == 1){

                $shiftPauseBegin = $shiftBegin->add(new \DateInterval('PT'.$pauseAfterHours.'H'));

                $shiftPauseEnd = $shiftPauseBegin->add(new \DateInterval('PT'.$pauseDuration.'H'));

            }

            $shiftEnd = $shiftBegin->add(new \DateInterval('PT'.$shiftHours.'H'));

            $objectIndex = random_int(0,1);

            $object = $objects[$objectIndex];

            for($j = 0;$j < 4;$j++){
                //first checkin
                if($j === 0){
                    $timeEntry = new TimeEntry();
                    $timeEntry->setTimeEntryType($checkInType);
                    $timeEntry->setMainCheckin(true);
                    $timeEntry->setEmployer($user);
                    $timeEntry->setCreatedAt($shiftBegin);
                    $timeEntry->setAutoCheckOut(null);
                    $timeEntry->setObject($object);


                    $this->entityManager->persist($timeEntry);
                    $this->entityManager->flush();
                }
                if($j === 1 && $takePause == 1){
                    $timeEntry = new TimeEntry();
                    $timeEntry->setTimeEntryType($pauseType);
                    $timeEntry->setMainCheckin(false);
                    $timeEntry->setEmployer($user);
                    $timeEntry->setCreatedAt($shiftPauseBegin);
                    $timeEntry->setAutoCheckOut(null);
                    $timeEntry->setObject($object);

                    $this->entityManager->persist($timeEntry);
                    $this->entityManager->flush();
                }
                if($j === 2 && $takePause == 1){
                    $timeEntry = new TimeEntry();
                    $timeEntry->setTimeEntryType($checkInType);
                    $timeEntry->setMainCheckin(false);
                    $timeEntry->setEmployer($user);
                    $timeEntry->setCreatedAt($shiftPauseEnd);
                    $timeEntry->setAutoCheckOut(null);
                    $timeEntry->setObject($object);

                    $this->entityManager->persist($timeEntry);
                    $this->entityManager->flush();
                }
                if($j === 3){
                    $timeEntry = new TimeEntry();
                    $timeEntry->setTimeEntryType($checkoutType);
                    $timeEntry->setMainCheckin(false);
                    $timeEntry->setEmployer($user);
                    $timeEntry->setCreatedAt($shiftEnd);
                    if(random_int(0,1) == 0){
                        $timeEntry->setAutoCheckOut(null);
                    }else{
                        $timeEntry->setAutoCheckOut(1);
                    }

                    $timeEntry->setObject($object);

                    $this->entityManager->persist($timeEntry);
                    $this->entityManager->flush();
                }

                $firstDate = $shiftBegin->add(new \DateInterval('PT20H'));
            }

        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
