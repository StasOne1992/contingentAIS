<?php

namespace App\Controller\Admission;

use App\Repository\AbiturientPetitionRepository;
use App\Repository\AbiturientPetitionStatusRepository;
use App\Repository\AdmissionPlanRepository;
use App\Repository\AdmissionRepository;
use App\Repository\AdmissionStatusRepository;
use App\Repository\FacultyRepository;
use App\Repository\RegionsRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admission/dashboard')]
#[IsGranted("ROLE_USER")]
class AdmissionDashboard extends AbstractController
{
    public function __construct(
        private AdmissionRepository                $admissionRepository,
        private AbiturientPetitionRepository       $abiturientPetitionRepository,
        private AbiturientPetitionStatusRepository $abiturientPetitionStatusRepository,
        private AdmissionStatusRepository          $admissionStatusRepository,
        private AdmissionPlanRepository            $admissionPlanRepository,
        private FacultyRepository                  $facultyRepository,
        //private PetitionLoadService                $petitionLoadService,
        private RegionsRepository                  $regionsRepository,

    )
    {
        $this->currentAdmission = $this->admissionRepository->findOneBy(['status' => $this->admissionStatusRepository->findOneBy(['Name' => 'RUNNING'])]);
        $this->currentAdmissionPlan = $this->currentAdmission->getAdmissionPlans();
        $this->facultyRepository->findAll();
        $this->currentAdmission->getAbiturientPetitions();
        $this->statusREGISTRED = $this->abiturientPetitionStatusRepository->findOneBy(['name' => 'REGISTERED']);
        $this->statusREJECTED = $this->abiturientPetitionStatusRepository->findOneBy(['name' => 'REJECTED']);
        $this->statusACCEPTED = $this->abiturientPetitionStatusRepository->findOneBy(['name' => 'ACCEPTED']);
        $this->statusCHECK = $this->abiturientPetitionStatusRepository->findOneBy(['name' => 'CHECK']);
        $this->statusRECOMMENDED = $this->abiturientPetitionStatusRepository->findOneBy(['name' => 'RECOMMENDED']);
        $this->statusDOCUMENTS_OBTAINED = $this->abiturientPetitionStatusRepository->findOneBy(['name' => 'DOCUMENTS_OBTAINED']);
        $this->statusINDUCTED = $this->abiturientPetitionStatusRepository->findOneBy(['name' => 'INDUCTED']);

        $this->SummaryMetricReport = array();
        $this->SummaryMetricReport['Count']['Title'] = 'Всего заявлений';
        $this->SummaryMetricReport['Count']['Icon'] = 'fa-solid fa-id-card';
        $this->SummaryMetricReport['Count']['Color'] = 'text-success';
        $this->SummaryMetricReport['Count']['Value'] = count($this->currentAdmission->getAbiturientPetitions());
        $this->SummaryMetricReport['Accepted']['Title'] = 'Принято к рассмотрению';
        $this->SummaryMetricReport['Accepted']['Icon'] = 'fa-solid fa-badge-check';
        $this->SummaryMetricReport['Accepted']['Color'] = 'text-success';
        $this->SummaryMetricReport['Accepted']['Value'] = count($this->abiturientPetitionRepository->findBy(['admission' => $this->currentAdmission, 'status' => $this->statusACCEPTED]));
        $this->SummaryMetricReport['Rejected']['Title'] = 'Отозвано';
        $this->SummaryMetricReport['Rejected']['Icon'] = 'fa-solid fa-shredder';
        $this->SummaryMetricReport['Rejected']['Color'] = 'text-warning';
        $this->SummaryMetricReport['Rejected']['Value'] = count($this->abiturientPetitionRepository->findBy(['admission' => $this->currentAdmission, 'status' => $this->statusREJECTED]));
        $this->SummaryMetricReport['Registred']['Title'] = 'Зарегистрировано';
        $this->SummaryMetricReport['Registred']['Icon'] = ' fa-solid fa-inbox-in';
        $this->SummaryMetricReport['Registred']['Color'] = 'text-danger';
        $this->SummaryMetricReport['Registred']['Value'] = count($this->abiturientPetitionRepository->findBy(['admission' => $this->currentAdmission, 'status' => $this->statusREGISTRED]));
        $this->SummaryMetricReport['Origins']['Title'] = 'Оригиналов';
        $this->SummaryMetricReport['Origins']['Icon'] = 'fa-solid fa-memo-circle-check';
        $this->SummaryMetricReport['Origins']['Color'] = 'text-success';
        $this->SummaryMetricReport['Origins']['Value'] = count($this->abiturientPetitionRepository->findBy(['admission' => $this->currentAdmission, 'documentObtained' => true]));
        $this->SummaryMetricReport['CanLoad']['Title'] = ' для загрузки';
        $this->SummaryMetricReport['CanLoad']['Icon'] = 'fa-solid fa-cloud-arrow-down';
        $this->SummaryMetricReport['CanLoad']['Color'] = 'text-success';
        $this->SummaryMetricReport['CanLoad']['Value'] = 0;//count($this->petitionLoadService->getNewPetitionList());
        $this->SummaryMetricReport['Unique']['Title'] = 'Абитуриентов всего';
        $this->SummaryMetricReport['Unique']['Icon'] = 'fa-solid fa-people';
        $this->SummaryMetricReport['Unique']['Color'] = 'text-success';
        $this->SummaryMetricReport['Unique']['Value'] = $this->abiturientPetitionRepository->getUniquePetitionCount($this->currentAdmission->getId());
        $this->SummaryMetricReport['NotReg']['Title'] = 'Не присвоен номер';
        $this->SummaryMetricReport['NotReg']['Icon'] = 'fa-solid fa-list-ol';
        $this->SummaryMetricReport['NotReg']['Color'] = 'text-danger';
        $this->SummaryMetricReport['NotReg']['Value'] = count($this->abiturientPetitionRepository->findBy(['admission' => $this->currentAdmission, 'localNumber' => null]));
        $this->ToLoad = $this->SummaryMetricReport['CanLoad']['Value'];

        $this->byRegionsReport = array();
        foreach ($this->regionsRepository->findAll() as $region) {
            $this->byRegionsReport[$region->getCode()]['Count'] = count($this->abiturientPetitionRepository->findBy(['admission' => $this->currentAdmission, 'Region' => $region, 'status' => $this->statusACCEPTED]));
            $this->byRegionsReport[$region->getCode()]['Title'] = $region->getName();
            array_multisort($this->byRegionsReport, SORT_DESC);
        }

        $counter = 0;
        foreach ($this->currentAdmissionPlan as $item) {
            $this->facultyPetitionCount[$counter]['Admission'] = $this->currentAdmission;
            $this->facultyPetitionCount[$counter]['Faculty'] = $item->getFaculty();
            $this->facultyPetitionCount[$counter]['Rejected'] = count($this->abiturientPetitionRepository->findBy(['admission' => $this->currentAdmission, 'Faculty' => $item->getFaculty(), 'status' => $this->statusREJECTED]));
            $this->facultyPetitionCount[$counter]['Registred'] = count($this->abiturientPetitionRepository->findBy(['admission' => $this->currentAdmission, 'Faculty' => $item->getFaculty(), 'status' => $this->statusREGISTRED]));
            $this->facultyPetitionCount[$counter]['Origins'] = count($this->abiturientPetitionRepository->findBy(['admission' => $this->currentAdmission, 'Faculty' => $item->getFaculty(), 'documentObtained' => true]));
            $this->facultyPetitionCount[$counter]['PetitionCount'] = $item->getFaculty()->getAbiturientPetitions()->count();
            $this->facultyPetitionCount[$counter]['Enroll'] = count($this->abiturientPetitionRepository->findBy(['admission' => $this->currentAdmission, 'Faculty' => $item->getFaculty(), 'status' => $this->statusRECOMMENDED]));
            $this->facultyPetitionCount[$counter]['Induct'] = count($this->abiturientPetitionRepository->findBy(['admission' => $this->currentAdmission, 'Faculty' => $item->getFaculty(), 'status' => $this->statusINDUCTED]));
            $this->facultyPetitionCount[$counter]['FISGIA'] = count($this->abiturientPetitionRepository->findBy(['admission' => $this->currentAdmission, 'Faculty' => $item->getFaculty(), 'LoadToFISGIA' => true]));
            $counter++;
        }
    }

    #[Route('/', name: 'app_admission_dashboard_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admission_dashboard/admission_dashboard.html.twig',
            [
                'facultyPetitionCount' => $this->facultyPetitionCount,
                'SummaryMetricReport' => $this->SummaryMetricReport,
                'ToLoad' => $this->ToLoad,
                'byRegionsReport' => $this->byRegionsReport,
            ]);
    }

    #[Route('/faculty/{admission}/{faculty}', name: 'app_admission_dashboard_faculty_petition_index', methods: ['GET'])]
    public function faculty_petition_index(Request $request, FacultyRepository $facultyRepository): Response
    {
        $admissionId = $request->get('admission');
        $faculty = $request->get('faculty');
        $petitionList = $this->abiturientPetitionRepository->findBy(['admission' => $admissionId, 'Faculty' => $faculty], ['educationDocumentGPA' => 'DESC']);
        $faculty = $facultyRepository->find($faculty);
        return $this->render('abiturient_petition/index.html.twig',
            [
                'abiturient_petitions' => $petitionList,
                'faculty' => $faculty,
                'facultyTitle' => $faculty->getName(),

            ]);
    }
}
