<?php

namespace App\Controller\EduPart\Student;

use App\Entity\Student;
use App\Entity\StudentGroups;
use App\Entity\User;
use App\Form\EduPart\StudentType;
use App\Form\EduPart\StudentImportType;
use App\Repository\CollegeRepository;
use App\Repository\StudentGroupsRepository;
use App\Repository\StudentRepository;
use App\Repository\UserRepository;
use App\Service\GlobalHelpersService;
use App\Service\StudentService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use App\Service\FileUploader;
use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;


#[Route('/student')]
class StudentController extends AbstractController
{
    public function __construct(
        private StudentService $studentService,
    )
    {
    }

    #[Route('/', name: 'app_student_index', methods: ['GET'])]
    #[IsGranted("ROLE_STAFF_STUDENT_R")]
    public function index(Request $request,StudentRepository $studentRepository,StudentGroupsRepository $studentGroupsRepository): Response
    {
        if($request->get('groupid')) {
            $group=$studentGroupsRepository->find($request->get('groupid'));
            $students = $studentRepository->findBy(['isActive' => true,'StudentGroup'=>$group], ['LastName' => 'ASC']);
        }
        else
        {
            $students = $studentRepository->findBy(['isActive' => true], ['LastName' => 'ASC']);
        }
        return $this->render('student/index.html.twig', [
            'students' => $students,
        ]);
    }

    #[Route('/new', name: 'app_student_new', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_STAFF_STUDENT_C")]
    public function new(Request $request, StudentRepository $studentRepository): Response
    {
        $student = new Student();
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $student->setUUID(uniqid());
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

    #[Route('/{id}/show', name: 'app_student_show', methods: ['GET'])]
    #[IsGranted("ROLE_STAFF_STUDENT_R")]
    public function show(Student $student): Response
    {
        //TODO: Убрать возможность смотреть чужих студентов

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

    #[Route('/{id}/setGroup', name: 'app_student_setGroup', methods: ['POST'])]
    #[IsGranted("ROLE_STAFF_STUDENT_U")]
    public function setGroup(Request $request, Student $student, StudentRepository $studentRepository, StudentGroupsRepository $studentGroupsRepository): Response
    {
        $student->setStudentGroup($studentGroupsRepository->find($request->get('group-select')));
        $studentRepository->save($student, true);
        return new Response(
            $student->getStudentGroup(),
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );;
        //return $this->redirectToRoute('app_contingent_document_edit', ['id'=>$request->get('contingentDocumentID')], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/delete', name: 'app_student_delete', methods: ['POST'])]
    #[IsGranted("ROLE_STAFF_STUDENT_D")]
    public function delete(Request $request, Student $student, StudentRepository $studentRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $student->getId(), $request->request->get('_token'))) {
            $studentRepository->remove($student, true);
        }

        return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/filluuid', name: 'app_student_fillUUID', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_STAFF_STUDENT_U")]
    public function fillUUID()
    {
        $this->studentService->fillStudentsUUID();
        return new Response();
    }


    #[Route('/fillemail', name: 'fillEmail', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_STAFF_STUDENT_U")]
    public function fillEmail()
    {
        $this->studentService->fillStudentsEmails();
        return new Response();
    }


    #[Route('/createUsers', name: 'app_student_createusers', methods: ['GET', 'POST'])]
    public function createusers(Request $request, StudentRepository $studentRepository, UserRepository $userRepository, GlobalHelpersService $globalHelpersService, UserPasswordHasherInterface $passwordHasher, CollegeRepository $collegeRepository): Response
    {
        if ($studentRepository->findAll()) {
            /***
             * @var Student $student
             */

            foreach ($studentRepository->findAll() as $student) {

                if ($student->getUsers()->isEmpty()) {
                    $user = new User();
                    $college = $collegeRepository->find(1);//ToDo: Написать метод для поиска колледжа
                    $genEmail=$globalHelpersService->translit($student->getLastName() . mb_substr($student->getFirstName(),0,1) .  mb_substr($student->getMiddleName(),0,1).'@' . $college->getSettingsStudentsDomain());
                    $user->setEmail($genEmail);
                    $genPass = $globalHelpersService->gen_password(10);
                    $password = $passwordHasher->hashPassword($user, $genPass);
                    $user->setPassword($password);
                    $user->setIsStudent();
                    $user->setStudent($student);
                    $user->setRoles(['ROLE_STUDENT']);
                    $student->setEmail($genEmail);
                    $studentRepository->save($student,true);
                    $userRepository->save($user,true);
                }
            }
        }

        return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
    }

}
