<?php

namespace App\Controller;

use App\Service\CompanyService;
use App\Service\EmployerService;
use App\Service\SettingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_MAIN")
 */
class EmployerController extends AbstractController
{
    private EmployerService $employerService;
    /**
     * @var \App\Entity\CompanyUser[]
     */
    private array $employers;
    private SettingService $settingService;
    private CompanyService $companyService;

    public function __construct(EmployerService $employerService, SettingService $settingService,CompanyService $companyService)
    {

        $this->employerService = $employerService;
        $this->employers = $this->employerService->getAllEmployers();
        $this->settingService = $settingService;
        $this->companyService = $companyService;
    }

    #[Route('/', name: 'mainBar')]
    public function index(): \Symfony\Component\HttpFoundation\Response
    {

        $authByPin = $this->settingService->authByPin();

        if ($authByPin == true) {
            return $this->render('mainBarPage/authByPin.html.twig',['companyObject'=>$this->companyService->getCurrentObject()]);
        } else {
            return $this->render('mainBarPage/employerList.html.twig', ['employers' => $this->employers]);
        }
    }


    #[Route('/openCheckinWindow', name: 'openCheckinWindow', methods: ['POST'])]
    public function checkEmployer(Request $request): \Symfony\Component\HttpFoundation\Response
    {

        $userID = $request->request->get('userID');
        $pin = $request->request->get('pin');
        $employer = null;
        //1. Define employer entity
        //check if auth by pin
        if ($pin !== null) {
            $employer = $this->employerService->getEmployerByPin($pin);
        } //check if auth by click on user
        else if ($userID != null) {
            if (str_contains($userID, 'user')) {
                $userID = str_replace('user', '', $userID);
                $employer = $this->employerService->getEmployerById($userID);
            } else {
                $error = "Benutzer wurde manipuliert. Starte bitte die Seite neu und führe den Checkin Vorgang erneut aus.";
            }
        }
        if($employer === null){
           $error = "Es wurde kein Mitarbeiter gefunden";
        }
        else{
            $workStart = $this->employerService->getEmployerWorkStartToday($employer);

            if ($workStart !== null) {
                $workStart = $workStart->format('m.d.Y G:i');
            }
            return $this->render('mainBarPage/checkEmployer.html.twig', [
                'employer' => $employer,
                'workStartToday' => $workStart
            ]);
        }

        echo json_encode([
            'error' => true,
            'message' => $error
        ]);

        exit;

    }

#[
Route('/checkin', name: 'checkin')]
    public function checkin(Request $request): \Symfony\Component\HttpFoundation\Response
{
    $userID = $request->request->get('userID');

    try {
        $this->employerService->userCheckAction($userID, "checkin");
        echo json_encode([
            'error' => null,
            'loadingMessage' => 'Du wirst eingecheckt.'
        ]);
        exit;
    } catch (\Exception $e) {
        echo json_encode([
            "error" => $e->getMessage()
        ]);
        exit;
    }
}

    #[Route('/pause', name: 'pause')]
    public function pause(Request $request): \Symfony\Component\HttpFoundation\Response
{
    $userID = $request->request->get('userID');
    try {
        $this->employerService->userCheckAction($userID, "pause");
        echo json_encode([
            'error' => null,
            'loadingMessage' => 'Deine Pause wird gestartet.'
        ]);
        exit;
    } catch (\Exception $e) {
        echo json_encode([
            "error" => $e->getMessage()
        ]);
        exit;
    }
}

    #[Route('/checkout', name: 'checkout')]
    public function checkout(Request $request): \Symfony\Component\HttpFoundation\Response
{

    $userID = $request->request->get('userID');
    try {
        $this->employerService->userCheckAction($userID, "checkout");
        echo json_encode([
            'error' => null,
            'loadingMessage' => 'Du wirst ausgecheckt.'
        ]);
        exit;
    } catch (\Exception $e) {
        echo json_encode([
            'error' => $e->getMessage(),
            'loadingMessage' => null
        ]);
        exit;
    }
}

    #[Route('/loading', name: 'loading')]
    public function loading(Request $request): \Symfony\Component\HttpFoundation\Response
{

    $loadingMessage = $request->request->get('loadingMessage');

    return $this->render('mainBarPage/checkLoading.html.twig', [
        'loadingMessage' => $loadingMessage
    ]);
}
}