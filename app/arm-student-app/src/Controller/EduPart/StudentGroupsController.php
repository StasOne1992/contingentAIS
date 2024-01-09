<?php

namespace App\Controller\EduPart;

use App\Controller\EduPart\Student;
use App\Entity\StudentGroups;
use App\Form\StudentGroupsType;
use App\Repository\StudentGroupsRepository;
use App\Service\TypicalDocuments;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\StudentGroupsService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/student-groups')]
#[IsGranted("ROLE_USER")]
class StudentGroupsController extends AbstractController
{
    public function __construct(
        private StudentGroupsService $studentGroupsService,
        public TypicalDocuments      $typicalDocuments,
    )
    {
    }

    #[Route('/', name: 'app_student_groups_index', methods: ['GET'])]
    public function index(StudentGroupsRepository $studentGroupsRepository): Response
    {
        //ToDo: Исправить проверку ролей на основе классов Security

        if ($this->getUser()!=null) {
            $user = $this->getUser();
            if (in_array('ROLE_CL', $user->getRoles()) and !in_array('ROLE_ROOT', $user->getRoles())) {
                dump('haverole CL');
            } else if (in_array('ROLE_ROOT', $user->getRoles())) {
                dump('RoleRoot');
            }
            return $this->render('student_groups/index.html.twig', [
                'student_groups' => $studentGroupsRepository->findAll(),
            ]);
        }

        return $this->render('student_groups/index.html.twig', [
            'student_groups' => array(),
        ]);
    }

    #[Route('/new', name: 'app_student_groups_new', methods: ['GET', 'POST'])]
    public function new(Request $request, StudentGroupsRepository $studentGroupsRepository): Response
    {
        $studentGroup = new StudentGroups();
        $form = $this->createForm(StudentGroupsType::class, $studentGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $studentGroupsRepository->save($studentGroup, true);

            return $this->redirectToRoute('app_student_groups_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('student_groups/new.html.twig', [
            'student_group' => $studentGroup,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/show', name: 'app_student_groups_show', methods: ['GET'])]
    public function show(StudentGroups $studentGroup, StudentGroupsService $StudentGroupsService, StudentGroupsRepository $StudentGroupsRepository): Response
    {

        /***
         * @var \App\Entity\Student $item
         */
                $studentGroup->socialPassport = $StudentGroupsService->generateSocialPasport($studentGroup);


        return $this->render('student_groups/show.html.twig', [
            'student_group' => $studentGroup,
            'student_list'=> $studentGroup->getActiveStudents(),
        ]);
    }

    #[Route('/{id}/show-students', name: 'app_student_groups_show_students', methods: ['GET'])]
    public function show_students(StudentGroups $studentGroup, StudentGroupsService $StudentGroupsService, StudentGroupsRepository $StudentGroupsRepository): Response
    {
        $students=$studentGroup->getStudents();

        return $this->render('student/index.html.twig', [
            'students' => $students,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_student_groups_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StudentGroups $studentGroup, StudentGroupsRepository $studentGroupsRepository): Response
    {
        $form = $this->createForm(StudentGroupsType::class, $studentGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $studentGroupsRepository->save($studentGroup, true);

            return $this->redirectToRoute('app_student_groups_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('student_groups/edit.html.twig', [
            'student_group' => $studentGroup,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_student_groups_delete', methods: ['POST'])]
    public function delete(Request $request, StudentGroups $studentGroup, StudentGroupsRepository $studentGroupsRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $studentGroup->getId(), $request->request->get('_token'))) {
            $studentGroupsRepository->remove($studentGroup, true);
        }

        return $this->redirectToRoute('app_student_groups_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/generateListToElearning', name: 'app_student_groups_list_to_elearning', methods: ['POST', 'GET'])]
    public function generateListToElearning(Request $request, StudentGroups $studentGroup): Response
    {
        return $this->studentGroupsService->generateElearningListToImport($studentGroup);
    }

    #[Route('/{id}/generateSchoolPortalList', name: 'app_student_groups_list_to_school_portal', methods: ['POST', 'GET'])]
    public function generateSchoolPortalList(Request $request, StudentGroups $studentGroup): Response
    {
        return $this->studentGroupsService->generateSchoolPortalList($studentGroup);
    }

    #[Route('/{id}/generatePerCo', name: 'app_student_groups_list_to_PerCo', methods: ['POST', 'GET'])]
    public function generatePerCo(Request $request, StudentGroups $studentGroup): Response
    {
        return $this->studentGroupsService->generatePerCo($studentGroup);
    }

    #[Route('/{id}/generateEnt', name: 'app_student_groups_list_to_ent', methods: ['POST', 'GET'])]
    public function generateEnt(Request $request, StudentGroups $studentGroup): Response
    {
        return $this->studentGroupsService->generateEnt($studentGroup);
    }

    #[Route('/{id}/generateAccessCardList', name: 'app_student_groups_generate_access_card_list', methods: ['POST', 'GET'])]
    public function generateAccessCardList(Request $request, StudentGroups $studentGroup): Response
    {


        $html = $this->renderView('_printtemplate.html.twig',
            [
                'content' => $this->typicalDocuments->generateSKUDRulesAccept($studentGroup)
            ]);
        $options = new Options();
        $options->set('defaultFont', '');

        $dompdf = new Dompdf($options);
        $options = $dompdf->getOptions();
        $dompdf->setOptions($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        return new Response (
            $dompdf->stream('resume', ["Attachment" => false]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/pdf']
        );
    }

    #[Route('/{id}/generateAccessCardIssueList', name: 'app_student_groups_generate_access_issue_card_list', methods: ['POST', 'GET'])]
    public function generateAccessCardIssueList(Request $request, StudentGroups $studentGroup): Response
    {


        $html = $this->renderView('_printtemplate.html.twig',
            [
                'content' => $this->typicalDocuments->generateSKUDissue($studentGroup)
            ]);
        $options = new Options();
        $options->set('defaultFont', '');

        $dompdf = new Dompdf($options);
        $options = $dompdf->getOptions();
        $dompdf->setOptions($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        return new Response (
            $dompdf->stream('resume', ["Attachment" => false]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/pdf']
        );
    }
}
