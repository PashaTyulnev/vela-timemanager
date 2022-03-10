<?php

namespace App\Controller;

use App\Security\EmployerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_BAR_MAIN")
 */
class EmployerController extends AbstractController
{
    private EmployerService $employerService;
    /**
     * @var \App\Entity\Employer[]
     */
    private array $employers;

    public function __construct(EmployerService $employerService){

        $this->employerService = $employerService;
        $this->employers = $this->employerService->getAllEmployers();
    }

    #[Route('/', name: 'mainBar' )]
    public function index(): \Symfony\Component\HttpFoundation\Response
    {


        return $this->render('mainBarPage/index.html.twig', ['employers'=>$this->employers]);
    }

    #[Route('/openCheckinWindow', name: 'openCheckinWindow', methods: ['POST'] )]
    public function checkEmployer(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $userID = $request->request->get('userID');

        if(str_contains($userID, 'user')){

            $userID = str_replace('user','',$userID);
            $employer = $this->employerService->getEmployerById($userID);

            return $this->render('mainBarPage/checkEmployer.html.twig',[
                'employer' => $employer
            ]);
        }
        else{
            return $this->render('mainBarPage/checkEmployer.html.twig',[
                'error' => "Benutzer wurde manipuliert. Starte bitte die Seite neu und führe den Checkin Vorgang erneut auf."
            ]);
                  }
    }

    #[Route('/checkin', name: 'checkin' )]
    public function checkin(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $userID = $request->request->get('userID');
        $this->employerService->userCheckAction($userID,"checkin");

        return $this->render('mainBarPage/index.html.twig', ['employers'=>$this->employers]);
    }

    #[Route('/pause', name: 'pause' )]
    public function pause(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $userID = $request->request->get('userID');
        $this->employerService->userCheckAction($userID,"pause");

        return $this->render('mainBarPage/index.html.twig', ['employers'=>$this->employers]);
    }

    #[Route('/checkout', name: 'checkout' )]
    public function checkout(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $userID = $request->request->get('userID');
        $this->employerService->userCheckAction($userID,"checkout");

        return $this->render('mainBarPage/index.html.twig', ['employers'=>$this->employers]);
    }
}