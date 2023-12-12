<?php

namespace App\Controller\EduPart\Student;

use App\Entity\PersonalDocuments;
use App\Entity\Student;
use App\Entity\StudentGroups;
use App\Entity\User;
use App\Form\EduPart\StudentImportType;
use App\Form\EduPart\StudentType;
use App\Repository\CollegeRepository;
use App\Repository\PersonalDocTypeListRepository;
use App\Repository\StudentGroupsRepository;
use App\Repository\StudentRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use App\Service\GlobalHelpersService;
use App\Service\StudentService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


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

    public function index(Request $request, StudentRepository $studentRepository, StudentGroupsRepository $studentGroupsRepository): Response
    {
        $user=$this->getUser();
        if($request->get('groupid')) {
            $group=$studentGroupsRepository->find($request->get('groupid'));
            $students = $studentRepository->findBy(['isActive' => true,'StudentGroup'=>$group], ['LastName' => 'ASC']);
        } else {
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
    public function import(Request $request, StudentRepository $studentRepository, FileUploader $fileUploader, PersonalDocTypeListRepository $personalDocTypeListRepository, StudentGroupsRepository $studentGroupsRepository): Response
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
                $students = array();
                $personaDocument = array();
                unset($lines[0]);
                foreach ($lines as $line) {
                    $temparray = str_getcsv($line);
                    $personalDocuments=array();
                    if (array_key_exists(1, $temparray)) {
                        $row['id'] = $temparray[0];
                        $row['first_name'] = $temparray[1];
                        $row['last_name'] = $temparray[2];
                        $row['middle_name'] = $temparray[3];
                        $row['family_type_id_id'] = $temparray[4];
                        $row['healtg_group_id_id'] = $temparray[5];
                        $row['gender_id'] = $temparray[6];
                        $row['number_zachetka'] = $temparray[7];
                        $row['number_stud_bilet'] = $temparray[8];
                        $row['birth_data'] = $temparray[9];
                        $row['phone_number'] = $temparray[10];
                        $row['email'] = $temparray[11];
                        $row['document_snils'] = $temparray[12];
                        $row['document_medical_id'] = $temparray[13];
                        $row['address_fact'] = $temparray[14];
                        $row['address_main'] = $temparray[15];
                        $row['is_active'] = $temparray[16];
                        $row['photo'] = $temparray[17];
                        $row['student_group_id'] = $temparray[18];
                        $row['is_orphan'] = $temparray[19];
                        $row['is_paid'] = $temparray[20];
                        $row['is_invalid'] = $temparray[21];
                        $row['is_poor'] = $temparray[22];
                        $row['pasport_number'] = $temparray[23];
                        $row['pasport_series'] = $temparray[24];
                        $row['pasport_date'] = $temparray[25];
                        $row['pasport_issue_organ'] = $temparray[26];
                        $row['education_document_type'] = $temparray[27];
                        $row['edu_doc_series'] = $temparray[28];
                        $row['edu_doc_number'] = $temparray[29];
                        $row['edu_doc_issue_organ'] = $temparray[30];
                        $row['edu_doc_date'] = $temparray[31];
                        $row['edu_doc_reg_number'] = $temparray[32];
                        $row['avg_mark'] = $temparray[33];
                        $row['is_without_parents'] = $temparray[34];
                        $row['abiturient_petition_id'] = $temparray[35];
                        $row['first_password'] = $temparray[36];
                        $row['uuid'] = $temparray[37];
                        $row['is_live_student_accommondation'] = $temparray[38];

                        $thisStudent = $studentRepository->findBy(['DocumentSnils' => $row['document_snils']]);
                        if (!is_null($thisStudent)) {
                            dump($thisStudent);
                        } else {
                            $student = new Student();
                            $student->setFirstName($row['first_name']);
                            $student->setLastName($row['last_name']);
                            $student->setMiddleName($row['middle_name']);
                            $student->setAddressMain($row['address_main']);
                            $student->setAddressFact($row['address_fact']);
                            $student->setBirthData(new \DateTime(date("d M Y", date(strtotime($row['birth_data'])))));
                            $student->setDocumentSnils($row['document_snils']);
                            $student->setPasportSeries($row['pasport_series']);
                            $student->setPasportNumber($row['pasport_number']);
                            $student->setPasportIssueOrgan($row['pasport_issue_organ']);
                            $student->setIsActive(true);
                            $student->setPasportDate(new \DateTime(date("d M Y", date(strtotime($row['pasport_date'])))));
                            $studentGroup = $studentGroupsRepository->findBy(['Code' => $row['student_group_id']]);
                            dump($row['student_group_id'], $studentGroup);

                            $students[] = $student;
                            $attestat = new PersonalDocuments();
                            $attestat->setStudent($student);
                            $docType = $personalDocTypeListRepository->findOneby(['Title' => $row['education_document_type']]);
                            $attestat->setDocumentType($docType);
                            $attestat->setDocumentNumber($row['edu_doc_number']);
                            $attestat->setDocumentSeries($row['edu_doc_series']);
                            $attestat->setDocumentOfficialSeal($row['edu_doc_issue_organ']);
                            $attestat->setDocumentIssueDate(new \DateTime(date("d M Y", date(strtotime($row['edu_doc_date'])))));
                            $personalDocuments[] = $attestat;
                        }
                    }
                }
            }
            dd($students, $personalDocuments);
            return $this->render('student/_import_form.html.twig', [
                'students' => $students,
            ]);
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
        if (!$student->getStudentGroup()) {
            $group = new StudentGroups();
            $group->setName("Группа не указана");
            $student->setStudentGroup($group);
        }

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
        );
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
