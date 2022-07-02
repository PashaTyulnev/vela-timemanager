<?php

namespace App\Controller;
use App\Repository\CompanyObjectRepository;
use App\Service\CompanyService;
use App\Service\EmployerService;
use App\Service\WorktimeService;
use Dompdf\Dompdf;
use Dompdf\Options;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    /**
     * @throws \Exception
     */
    #[Route('/load-worktime', name: 'loadWorktime')]
    public function loadWorkTime(Request $request,$month = null,$year = null): \Symfony\Component\HttpFoundation\Response
    {
        $dateNow = new \DateTime();
        $yearNow = $dateNow->format('Y');
        $monthNow = $dateNow->format('m');

        $object = $request->request->get("objectId");
        $employer = null;

        if($request->request->get("date") !== null){
            $date = $request->request->get("date");
            $date = new \DateTime($date.'-1');
            $month = $date->format('m');
            $year = $date->format('Y');

            $yearNow = $year;
            $monthNow = $month;
        }

        if($request->request->get("employer") !== null){
            $employer = $request->request->get("employer");
        }


        //wenn alle Zeiten voll allen Arbeitern
        if(is_numeric($employer)){
            $employer = (int)$employer;
        }else{
            $employer = null;
        }

        $timeEntriesOfObject = $this->worktimeService->getWorkTimeOfObject($object,$month,$year,$employer);
        $monthsForSelect = $this->worktimeService->getMonthsForSelect($object);
        $allEmployers = $this->employerService->getAllEmployers();


        return $this->render('admin/worktime/loadWorktime.html.twig', [
            'timeEntries' => $timeEntriesOfObject,
            'objectId' => $object,
            'monthsOfSelect' => $monthsForSelect,
            'yearNow' => $yearNow,
            'monthNow' => $monthNow,
            'employers' => $allEmployers,
            'selectedEmployer' => $employer
        ]);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] #[Route('/export-pdf', name: 'export-pdf')]
    public function exportPdf(Request $request,CompanyObjectRepository $objectRepository)
    {

        $dateNow = new \DateTime();
        $yearNow = $dateNow->format('Y');
        $monthNow = $dateNow->format('m');
        $employer = null;
        //hier kommen die benötigten daten für den pdf export an
        $object = $request->request->get("objectId");

        if($request->request->get("datetime") !== null){
            $date = $request->request->get("datetime");

            $date = new \DateTime($date.'-1');
            $month = $date->format('m');
            $year = $date->format('Y');

            $yearNow = $year;
            $monthNow = $month;
        }

        if($request->request->get("employer") !== null){
            $employer = $request->request->get("employer");
        }
        //wenn alle Zeiten voll allen Arbeitern
        if(is_numeric($employer)){
            $employer = (int)$employer;
        }else{
            $employer = null;
        }

        $timeEntriesOfObject = $this->worktimeService->getWorkTimeOfObject($object,$monthNow,$yearNow,$employer);

        $objectName=$objectRepository->findOneBy(['id'=>$object]);
        $objectName=$objectName->getStreet() . " " . $objectName->getNumber();

        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        if($monthNow == "01"){
            $monthName = "Januar";
        }
        if($monthNow == "02"){
            $monthName = "Februar";
        }
        if($monthNow == "03"){
            $monthName = "März";
        }
        if($monthNow == "04"){
            $monthName = "April";
        }
        if($monthNow == "05"){
            $monthName = "Mai";
        }
        if($monthNow == "06"){
            $monthName = "Juni";
        }
        if($monthNow == "07"){
            $monthName = "Juli";
        }
        if($monthNow == "08"){
            $monthName = "August";
        }
        if($monthNow == "09"){
            $monthName = "September";
        }
        if($monthNow == "10"){
            $monthName = "Oktober";
        }
        if($monthNow == "11"){
            $monthName = "November";
        }
        if($monthNow == "12"){
            $monthName = "Dezember";
        }

        if($employer != null){
            $employer = $this->employerService->getEmployerById($employer);
        }

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        // Retrieve the HTML generated in our twig file

        $html = $this->renderView('admin/worktime/pdf/pdf.html.twig', [
            'timeEntries'=>$timeEntriesOfObject,
            'objectName' =>$objectName,
            'month' => $monthName ." " .$yearNow,
            'employer' => $employer,
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4','portrait');

        // Render the HTML as PDF
        $dompdf->render();

//        // Output the generated PDF to Browser (inline view)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => false
        ]);

        exit;
    }

    #[Route('/edit-time-entry', name: 'editTimeEntry')]
    public function editTimeEntry(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $uid = $request->request->get("uid");

        $timeEntryGroup = $this->worktimeService->getTimeEntryGroup($uid);
        $employer = $this->worktimeService->getEmployerByUid($uid);

        return $this->render('admin/worktime/editTimeEntry.html.twig', [
            'timeEntries'=>$timeEntryGroup,
            'employer' => $employer
        ]);
    }

    #[Route('/save-time-entry')]
    public function saveTimeEntry(Request $request): \Symfony\Component\HttpFoundation\Response
    {

        $allTimeEntriesData = $request->request->all();

        $this->worktimeService->saveTimeEntryChange($allTimeEntriesData);

        exit;

    }

    #[Route('/delete-time-entry')]
    public function deleteTimeEntry(Request $request): \Symfony\Component\HttpFoundation\Response
    {

        $uid = $request->request->get('uid');

        $this->worktimeService->deleteTimeEntries($uid);

        exit;

    }
}