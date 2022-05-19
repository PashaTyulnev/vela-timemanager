<?php

namespace App\Controller;

use App\Service\CompanyService;
use App\Service\EmployerService;
use App\Service\ObjectService;
use App\Service\WorktimeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Config\Framework\RequestConfig;

/**
 * @IsGranted("ROLE_ADMIN")
 */
class AdminController extends AbstractController
{

    private EmployerService $employerService;
    private WorktimeService $worktimeService;
    private CompanyService $companyService;
    private ObjectService $objectService;

    public function __construct(EmployerService $employerService, WorktimeService $worktimeService, CompanyService $companyService, ObjectService $objectService){

        $this->employerService = $employerService;
        $this->worktimeService = $worktimeService;
        $this->companyService = $companyService;
        $this->objectService = $objectService;
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

    #[Route('/new-employer', name: 'newEmployer')]
    public function newEmployer(Request $request): \Symfony\Component\HttpFoundation\Response
    {


        try{
            $newEmployer = $this->employerService->createNewEmployer($request);

            return $this->render('admin/employers.html.twig', [
                'allEmployers' => $this->employerService->getAllEmployers(),
                'success' => $newEmployer['success']
            ]);

        }
        catch (\Exception $e){
            return $this->render('admin/employers.html.twig', [
                'allEmployers' => $this->employerService->getAllEmployers(),
                'error' => $e->getMessage()
            ]);
        }
    }

    #[Route('/worktime', name: 'adminWorktime')]
    public function adminWorktime(): \Symfony\Component\HttpFoundation\Response
    {
        $allCompanyObjects = $this->companyService->getCompanyObjects();
        return $this->render('admin/worktime/worktime.html.twig', [
        'allObjects' => $allCompanyObjects
        ]);
    }

    #[Route('/objects', name: 'adminObjects')]
    public function loadObjects(): \Symfony\Component\HttpFoundation\Response
    {
        $allCompanyObjects = $this->companyService->getCompanyObjects();
        return $this->render('admin/objects/objects.html.twig', [
            'allObjects' => $allCompanyObjects
        ]);
    }

    #[Route('/new-object', name: 'newObject')]
    public function newObject(Request $request): \Symfony\Component\HttpFoundation\Response
    {

        try{
            $newObject  = $this->objectService->createNewObject($request);

            return $this->redirectToRoute('adminObjects');

        }
        catch (\Exception $e){

            return $this->render('admin/objects/objects.html.twig', [
                'allObjects' => $this->companyService->getCompanyObjects(),
                'error' => $e->getMessage()
            ]);
        }
    }
}