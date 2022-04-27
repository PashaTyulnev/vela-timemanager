<?php

namespace App\Controller;
use App\Repository\CompanyObjectRepository;
use App\Service\CompanyService;
use App\Service\EmployerService;
use App\Service\WorktimeService;
use Dompdf\Dompdf;
use Dompdf\Options;
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

    #[Route('/load-worktime', name: 'loadWorktime')]
    public function loadWorktime(Request $request,$month = null,$year = null): \Symfony\Component\HttpFoundation\Response
    {

        $object = $request->request->get("objectId");

        $timeEntriesOfObject = $this->worktimeService->getWorkTimeOfObject($object,$month,$year);
        return $this->render('admin/worktime/loadWorktime.html.twig', [
            'timeEntries' => $timeEntriesOfObject,
            'objectId' => $object
        ]);
    }

    #[Route('/export-pdf', name: 'export-pdf')]
    public function exportPdf(Request $request,CompanyObjectRepository $objectRepository): \Symfony\Component\HttpFoundation\Response
    {
        //hier kommen die benötigten daten für den pdf export an
        $object = $request->request->get("objectId");
        $month = $request->request->get("month");

        $timeEntriesOfObject = $this->worktimeService->getWorkTimeOfObject($object);
        $objectName=$objectRepository->findOneBy(['id'=>$object]);
        $objectName=$objectName->getName();

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');


        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('admin/worktime/pdf/pdf.html.twig', [
            'timeEntries'=>$timeEntriesOfObject,
            'objectName' =>$objectName
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (inline view)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => false
        ]);
        exit(0);
        return $this->redirectToRoute('export-pdf');

    }
}