<?php

namespace App\Controller;
use App\Service\CompanyService;
use App\Service\EmployerService;
use App\Service\WorktimeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_ADMIN")
 */
class WorktimeController extends AbstractController
{
    private EmployerService $employerService;
    private WorktimeService $worktimeService;
    private CompanyService $companyService;

    public function __construct(EmployerService $employerService, WorktimeService $worktimeService, CompanyService $companyService){

        $this->employerService = $employerService;
        $this->worktimeService = $worktimeService;
        $this->companyService = $companyService;
    }

    #[Route('/load-worktime', name: 'loadWorktime')]
    public function loadWorktime(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $object = $request->request->get("objectId");

        $timeEntriesOfObject = $this->worktimeService->getWorkTimeOfObject($object);
        return $this->render('admin/worktime/loadWorktime.html.twig', [
            'timeEntries' => $timeEntriesOfObject
        ]);
    }
}