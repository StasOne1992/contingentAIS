<?php

namespace App\Controller\EduPart\Student;

use App\Entity\Student;
use App\Form\EduPart\StudentType;
use App\Form\EduPart\StudentImportType;
use App\Repository\StudentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use App\Service\FileUploader;



#[Route('/student')]
class StudentController extends AbstractController
{
    #[Route('/', name: 'app_student_index', methods: ['GET'])]
    #[IsGranted("ROLE_STAFF_STUDENT_R")]
    public function index(StudentRepository $studentRepository): Response
    {
        return $this->render('student/index.html.twig', [
            'students' => $studentRepository->findBy([], ['LastName' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'app_student_new', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_STAFF_STUDENT_С")]
    public function new(Request $request, StudentRepository $studentRepository): Response
    {
        $student = new Student();
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $studentRepository->save($student, true);
            return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('student/new.html.twig', [
            'student' => $student,
            'form' => $form,
        ]);
    }

    #[Route('/import', name: 'app_student_import', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_STAFF_STUDENT_IMP")]
    public function import(Request $request, StudentRepository $studentRepository, FileUploader $fileUploader): Response
    {
        $student = new Student();
        $form = $this->createForm(StudentImportType::class, $student);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('brochure')->getData();
            if ($brochureFile) {
                $brochureFileName = $fileUploader->upload($brochureFile);

                $fileName = new File($this->getParameter('student_import_directory') . '/' . $brochureFileName);
                $csvData = file_get_contents($fileName);
                $lines = explode(PHP_EOL, $csvData);
                $array = array();
                foreach ($lines as $line) {
                    $array[] = str_getcsv($line);
                }
                dd($array);
            }

        return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
    }

return $this->renderForm('student/import.html.twig', ['student' => $student,
'form' => $form,]);
}

#[Route('/{id}', name: 'app_student_show', methods: ['GET'])]
#[IsGranted("ROLE_STAFF_STUDENT_R")]
    public function show(Student $student): Response
{
    $student->getPersonalDocuments()->getValues();
    $student->getCharacteristics()->getValues();
    $student->getLegalRepresentatives()->getValues();
    $student->getContingentDocuments()->getValues();
    return $this->render('student/show.html.twig', [
        'student' => $student,
    ]);
}

    #[Route('/{id}/edit', name: 'app_student_edit', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_STAFF_STUDENT_U")]
    public function edit(Request $request, Student $student, StudentRepository $studentRepository): Response
{
    $form = $this->createForm(StudentType::class, $student);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $studentRepository->save($student, true);
        return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('student/edit.html.twig', [
        'student' => $student,
        'form' => $form,
    ]);
}

    #[Route('/{id}', name: 'app_student_delete', methods: ['POST'])]
    #[IsGranted("ROLE_STAFF_STUDENT_D")]
    public function delete(Request $request, Student $student, StudentRepository $studentRepository): Response
{
    if ($this->isCsrfTokenValid('delete' . $student->getId(), $request->request->get('_token'))) {
        $studentRepository->remove($student, true);
    }

    return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
}
}
