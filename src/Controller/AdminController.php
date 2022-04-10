<?php

namespace App\Controller;

use App\Service\CompanyService;
use App\Service\EmployerService;
use App\Service\WorktimeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
/**
 * @IsGranted("ROLE_ADMIN")
 */
class AdminController extends AbstractController
{

    private EmployerService $employerService;
    private WorktimeService $worktimeService;
    private CompanyService $companyService;

    public function __construct(EmployerService $employerService, WorktimeService $worktimeService, CompanyService $companyService){

        $this->employerService = $employerService;
        $this->worktimeService = $worktimeService;
        $this->companyService = $companyService;
    }

    #[Route('/admin', name: 'adminDashboard')]
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('admin/dashboard.html.twig', [

        ]);
    }

    #[Route('/employers', name: 'adminEmployers')]
    public function adminEmployers(): \Symfony\Component\HttpFoundation\Response
    {

        return $this->render('admin/employers.html.twig', [
        'allEmployers' => $this->employerService->getAllEmployers()
        ]);
    }

    #[Route('/worktime', name: 'adminWorktime')]
    public function adminWorktime(): \Symfony\Component\HttpFoundation\Response
    {
        $allCompanyObjects = $this->companyService->getCompanyObjects();
        return $this->render('admin/worktime/worktime.html.twig', [
        'allObjects' => $allCompanyObjects
        ]);
    }
}