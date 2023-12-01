<?php

namespace App\Controller\Admission;

use App\Controller\IsGranted;
use App\Entity\AbiturientPetition;
use App\Entity\AbiturientPetitionStatus;
use App\Entity\Admission;
use App\Entity\AdmissionExaminationResult;
use App\Entity\AdmissionPlan;
use App\Entity\College;
use App\Entity\ContingentDocument;
use App\Entity\Faculty;
use App\Entity\PersonalDocuments;
use App\Entity\Student;
use App\Entity\User;
use App\Form\AbiturientPetitionLoadType;
use App\Form\AbiturientPetitionType;
use App\Repository\AbiturientPetitionRepository;
use App\Repository\AbiturientPetitionStatusRepository;
use App\Repository\AdmissionExaminationRepository;
use App\Repository\AdmissionPlanRepository;
use App\Repository\AdmissionRepository;
use App\Repository\CollegeRepository;
use App\Repository\ContingentDocumentRepository;
use App\Repository\FacultyRepository;
use App\Repository\PersonalDocTypeListRepository;
use App\Repository\PersonalDocumentsRepository;
use App\Repository\genderRepository;
use App\Repository\StudentRepository;
use App\Repository\UserRepository;
use App\Service\Admission\PetitionLoadService;
use App\Service\Admission\PetitionToSiteService;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\AdmissionExaminationResultRepository;
use App\Service\GlobalHelpersService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;
use DateTime;
use function PHPUnit\Framework\isNull;

#[Route('/admission/petition')]
class AbiturientPetitionController extends AbstractController
{
    public function __construct(
        private AbiturientPetitionRepository         $abiturientPetitionRepository,
        private FacultyRepository                    $facultyRepository,
        private AdmissionRepository                  $admissionRepository,
        private AbiturientPetitionStatusRepository   $abiturientPetitionStatusRepository,
        private AdmissionExaminationResultRepository $admissionExaminationResultRepository,
        private GenderRepository                     $genderRepository,
        private AdmissionExaminationRepository       $admissionExaminationRepository,
        private AdmissionPlanRepository              $admissionPlanRepository,
        private ContingentDocumentRepository         $contingentDocumentRepository,
        private StudentRepository                    $studentRepository,
        private UserRepository                       $userRepository,
        private GlobalHelpersService                 $globalHelpersService,
        private PersonalDocumentsRepository          $personalDocumentsRepository,
        private PersonalDocTypeListRepository        $personalDocTypeListRepository,
        private CollegeRepository                    $cllegeRepository,
        private Security                             $security,

    )
    {
        $this->user = $this->security->getUser();

    }

    #[Route('/', name: 'app_abiturient_petition_index', methods: ['GET'])]
    #[IsGranted("ROLE_STAFF_AB_PETITIONS_R")]
    public function index(AbiturientPetitionRepository $abiturientPetitionRepository): Response
    {
        $this->abiturientPetitionStatusRepository->findAll();
        return $this->render('abiturient_petition/index.html.twig', [
            'abiturient_petitions' => $abiturientPetitionRepository->findAll(),
        ]);
    }


    #[Route('/new', name: 'app_abiturient_petition_new', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_STAFF_AB_PETITIONS_MANUAL_C")]
    public function new(Request $request, AbiturientPetitionRepository $abiturientPetitionRepository): Response
    {
        $abiturientPetition = new AbiturientPetition();
        $form = $this->createForm(AbiturientPetitionType::class, $abiturientPetition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $abiturientPetitionRepository->save($abiturientPetition, true);

            return $this->redirectToRoute('app_abiturient_petition_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('abiturient_petition/new.html.twig', [
            'abiturient_petition' => $abiturientPetition,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/show', name: 'app_abiturient_petition_show', methods: ['GET'])]
    #[IsGranted("ROLE_STAFF_AB_PETITIONS_R")]
    public function show(AbiturientPetition $abiturientPetition): Response
    {
        $this->abiturientPetitionStatusRepository->findAll();
        return $this->render('abiturient_petition/show.html.twig', [
            'abiturient_petition' => $abiturientPetition,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_abiturient_petition_edit', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_STAFF_AB_PETITIONS_U")]
    public function edit(Request $request, AbiturientPetition $abiturientPetition): Response
    {
        $this->genderRepository->findAll();
        $this->abiturientPetitionStatusRepository->findAll();

        if (!isNull($abiturientPetition->getAdmissionPlanPosition())) {
            $admissionExamination = $this->admissionExaminationRepository->findBy(['AdmissionPlanPosition' => $abiturientPetition->getAdmissionPlanPosition()->getId()]);
            $abiturientPetition->getResult();
        }

        $form = $this->createForm(AbiturientPetitionType::class, $abiturientPetition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->abiturientPetitionRepository->save($abiturientPetition, true);

            return $this->redirectToRoute('app_abiturient_petition_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('abiturient_petition/edit.html.twig', [
            'abiturient_petition' => $abiturientPetition,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_abiturient_petition_delete', methods: ['POST'])]
    #[IsGranted("ROLE_STAFF_AB_PETITIONS_D")]
    public function delete(Request $request, AbiturientPetition $abiturientPetition, AbiturientPetitionRepository $abiturientPetitionRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $abiturientPetition->getId(), $request->request->get('_token'))) {
            $abiturientPetitionRepository->remove($abiturientPetition, true);
        }

        return $this->redirectToRoute('app_abiturient_petition_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Загрузка заявлений из ВИС Прием в ПОО
     * @param Request $request
     * @param PetitionLoadService $petitionLoadService
     * @return Response
     */

    #[Route('/loadFromVIS', name: 'app_abiturient_petition_loadFromVIS', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_STAFF_AB_PETITIONS_VIS")]
    public function loadFromVIS(Request $request, PetitionLoadService $petitionLoadService): Response
    {
        $loadPetitions = $petitionLoadService->getNewPetitionList();
        $form = $this->createForm(AbiturientPetitionLoadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $petitionLoadService->submitPetitionList($loadPetitions);
            return $this->redirectToRoute('app_abiturient_petition_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('abiturient_petition/loadpetitions.html.twig', [
            'form' => $form,
            'fetched_petition' => $loadPetitions
        ]);
    }

    /**
     * Загрузть одно заявление из ВИС Прием в ПОО
     * @param Request $request
     * @param AbiturientPetition $abiturientPetition
     * @param AbiturientPetitionRepository $abiturientPetitionRepository
     * @param PetitionLoadService $petitionLoadService
     * @return Response
     */
    #[Route('/updatePetitionFromVIS/{GUID}', name: 'app_abiturient_petition_updateFromVIS', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_STAFF_AB_PETITIONS_VIS")]
    public function updatePetitionFromVIS(Request $request, AbiturientPetition $abiturientPetition, AbiturientPetitionRepository $abiturientPetitionRepository, petitionLoadService $petitionLoadService): Response
    {
        $petitionLoadService->pubUpdatePetitionAsObject($abiturientPetition);
        return $this->redirectToRoute('app_abiturient_petition_edit', ['id' => $abiturientPetition->getId()], Response::HTTP_SEE_OTHER);
    }

    /**
     * @param Request $request
     * @param AbiturientPetitionRepository $abiturientPetitionRepository
     * @param PetitionLoadService $petitionLoadService
     * @return Response
     */
    #[Route('/updateAllFromVIS', name: 'app_abiturient_petition_updateAllFromVIS', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_STAFF_AB_PETITIONS_VIS")]
    public function updateAllFromVIS(Request $request, AbiturientPetitionRepository $abiturientPetitionRepository, petitionLoadService $petitionLoadService): Response
    {
        $loadPetitions = $petitionLoadService->getPetitionListToUpdate();
        $form = $this->createForm(AbiturientPetitionLoadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $petitionLoadService->updatePetitionList($loadPetitions);
            return $this->redirectToRoute('app_abiturient_petition_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('abiturient_petition/updatePetitionsAll.html.twig', [
            'form' => $form,
            'fetched_petition' => $loadPetitions
        ]);
    }


    /**
     *  Загрузка на сайт ПОО через API
     * @param Request $request
     * @param AbiturientPetitionRepository $abiturientPetitionRepository
     * @param PetitionToSiteService $petitionToSiteService
     * @return Response
     */
    #[Route('/uploadToSite', name: 'app_abiturient_petition_uploadToSite', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_STAFF_AB_PETITIONS_SITE")]
    public function uploadToSite(Request $request, AbiturientPetitionRepository $abiturientPetitionRepository, PetitionToSiteService $petitionToSiteService): Response
    {
        $petitionToSiteService->loadPetitionToSite();
        return $this->redirectToRoute('app_abiturient_petition_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Рейтинг заявлений
     * @param Request $request
     * @param AbiturientPetitionRepository $abiturientPetitionRepository
     * @return Response
     */
    #[Route('/rating', name: 'app_abiturient_petition_show_by_admission_and_faculty', methods: ['GET'])]
    #[IsGranted("ROLE_STAFF_AB_PETITIONS_R")]
    public function showByAdmissionPlan(Request $request, AbiturientPetitionRepository $abiturientPetitionRepository): Response
    {
        $petitionStatus = $this->abiturientPetitionStatusRepository->findOneBy(['name' => 'ACCEPTED']);
        $admission = $this->admissionRepository->find($request->get('admissionID'));
        $facultyID = $this->facultyRepository->find($request->get('facultyID'));
        $abiturientPetitionList = $this->abiturientPetitionRepository->findBy(['admission' => $admission, 'Faculty' => $facultyID, 'documentObtained' => true, 'status' => $petitionStatus], ['educationDocumentGPA' => 'DESC']);
        return $this->render('abiturient_petition/index.html.twig', [
            'abiturient_petitions' => $abiturientPetitionList,
            'facultyTitle' => $request->get('facultyTitle'),
            'enroll' => true
        ]);
    }


    #[Route('/ratingwithexam', name: 'app_abiturient_petition_show_by_admission_and_faculty_and_exam_result', methods: ['GET'])]
    #[IsGranted("ROLE_STAFF_AB_PETITIONS_R")]
    public function showByAdmissionPlanAndExamResutl(Request $request): Response
    {
        /**
         * @var AbiturientPetitionStatus $petitionStatus
         * @var Admission $admission
         * @var Faculty $facultyID
         * @var AdmissionPlan $admissionPlanId
         */

        $petitionStatus = $this->abiturientPetitionStatusRepository->findOneBy(['name' => 'ACCEPTED']);
        $admission = $this->admissionRepository->findBy(['id' => $request->get('admissionID')]);
        $facultyID = $this->facultyRepository->find($request->get('facultyID'));
        $admissionPlanId = $this->admissionPlanRepository->findOneBy(['admission' => $admission, 'faculty' => $facultyID]);

        $abiturientPetitionList = $this->abiturientPetitionRepository->findBy(['AdmissionPlanPosition' => $admissionPlanId, 'documentObtained' => true, 'status' => $petitionStatus], ['educationDocumentGPA' => 'DESC']);

        return $this->render('abiturient_petition/index.html.twig', [
            'abiturient_petitions' => $abiturientPetitionList,
            'facultyTitle' => $request->get('facultyTitle'),
            'showExamResul' => true,
            'enroll' => true,
        ]);
    }


    #[Route('/enroll_index', name: 'app_abiturient_petition_show_by_admission_and_faculty_enroll', methods: ['GET'])]
    #[IsGranted("ROLE_STAFF_AB_PETITIONS_R")]
    public function showEnroll(Request $request, AbiturientPetitionRepository $abiturientPetitionRepository): Response
    {
        $petitionStatus = $this->abiturientPetitionStatusRepository->findOneBy(['name' => 'RECOMMENDED']);
        $admission = $this->admissionRepository->find($request->get('admissionID'));
        $facultyID = $this->facultyRepository->find($request->get('facultyID'));
        $abiturientPetitionList = $this->abiturientPetitionRepository->findBy(['admission' => $admission, 'Faculty' => $facultyID, 'documentObtained' => true, 'status' => $petitionStatus], ['educationDocumentGPA' => 'DESC']);
        $contingentDocuments = $this->contingentDocumentRepository->findBy(['isActive' => false, 'College' => $admission->getCollege()->getId()]);
        return $this->render('abiturient_petition/index.html.twig', [
            'abiturient_petitions' => $abiturientPetitionList,
            'facultyTitle' => $request->get('facultyTitle'),
            'inducted' => true,
            'contingentDocumentsList' => $contingentDocuments,
        ]);
    }


    #[Route('/{id}/enroll', name: 'app_abiturient_petition_enroll', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_STAFF_AB_PETITIONS_ENROLL")]
    public function enroll(AbiturientPetition $abiturientPetition): Response
    {
        $petitionStausRecommended = $this->abiturientPetitionStatusRepository->findOneBy(['name' => 'RECOMMENDED']);
        $abiturientPetition->setStatus($petitionStausRecommended);
        $abiturientPetition->setLockUpdateFormVIS(true);

        $this->abiturientPetitionRepository->save($abiturientPetition, true);

        $admissionID = $abiturientPetition->getAdmission()->getId();
        $facutltyID = $abiturientPetition->getFaculty()->getId();
        $facutltyTitle = $abiturientPetition->getFaculty()->getName();

        return $this->redirectToRoute('app_abiturient_petition_show_by_admission_and_faculty',
            ['admissionID' => $admissionID,
                'facultyID' => $facutltyID,
                'facultyTitle' => $facutltyTitle],
            Response::HTTP_SEE_OTHER);
    }

    /*
     * Метод "Зачисление в систему". Выполняет следующие действия:
     * - Создает нового студента
     * - Добавляет пользователя
     * - Записывает первый пароль в профиль студента
     * - Добавляет студента в приказ о зачислении
     * - Прикрепляет заявление абитуриента к профилю студента
     * - Добавляет аттестат в перечень персональных документов
     * - Полностью блокирует заявление абитуриента для редактирования
     */
    #[Route('/induct', name: 'app_abiturient_petition_induct', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_STAFF_AB_PETITIONS_INDUCT")]
    public function induct(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        /**
         * @var Boolean $flush
         * Переменная для тестирования эмулирует работу с БД,
         * но не записывает информацию в БД.
         * В режиме Production должна быть равна True
         */

        //TODO: Выпилить эту переменную перед сдачей в Production

        $flush = true;

        /**
         * Секция извлечения информации из POST запроса
         */

        $abiturientPetition = $this->abiturientPetitionRepository->find($request->get('PetitionId'));
        $contingentDocument = $this->contingentDocumentRepository->find($request->get('contingentDocument'));

        /*
         * Секция проверки минимальных условий
         */

        if ($this->abiturientPetitionStatusRepository->findOneBy(['name' => 'RECOMMENDED']) != $abiturientPetition->getStatus()) {
            throw new AccessDeniedHttpException("Запрещено зачислять заявления со статусом отличном от 'Рекомендован' ");
        }


        /**
         * Секция создания студента
         */
        if ($this->studentRepository->findOneBy(['DocumentSnils' => (int)str_replace('-', '', str_replace(' ', '', $abiturientPetition->getDocumentSNILS()))])) {
            $student = $this->studentRepository->findOneBy(['DocumentSnils' => (int)str_replace('-', '', str_replace(' ', '', $abiturientPetition->getDocumentSNILS()))]);
        } else {
            $student = new Student();
        }
        $student->setFirstName($abiturientPetition->getFirstName());
        $student->setLastName($abiturientPetition->getLastName());
        $student->setMiddleName($abiturientPetition->getMiddleName());
        $student->setPhoneNumber($abiturientPetition->getPhone());
        $student->setBirthData($abiturientPetition->getBirthDate());
        $student->setDocumentSnils((int)str_replace('-', '', str_replace(' ', '', $abiturientPetition->getDocumentSNILS())));
        $student->setPasportNumber($abiturientPetition->getPasportNumber());
        $student->setPasportSeries($abiturientPetition->getPasportSeries());
        $student->setPasportDate($abiturientPetition->getPasportDateObtain());
        $student->setPasportIssueOrgan($abiturientPetition->getPasportIssueOrgan());
        $student->setAddressFact($abiturientPetition->getregistrationAddress());
        $student->setAddressMain($abiturientPetition->getregistrationAddress());
        $student->setSex($abiturientPetition->getGender());
        $student->setIsOrphan($abiturientPetition->isIsOrphan());
        $student->setIsInvalid($abiturientPetition->isIsInvalid());
        $student->setIsPoor($abiturientPetition->isIsPoor());
        $student->setAbiturientPetition($abiturientPetition);
        $student->setUUID(uniqid());

        $this->studentRepository->save($student, $flush);

        /**
         * Секция добавления студента в приказ
         */

        $contingentDocument->addStudent($student);
        $this->contingentDocumentRepository->save($contingentDocument, $flush);

        /**
         * Секция создания пользователя
         */


        $studentFIO = $this->globalHelpersService->translit(iconv('windows-1251', 'UTF-8', iconv('UTF-8', 'windows-1251', $student->getLastName()) . iconv('UTF-8', 'windows-1251', $student->getFirstName())[0] . iconv('UTF-8', 'windows-1251', $student->getMiddleName())[0]));

        $login = $this->checkUser($abiturientPetition, $studentFIO);

        if ($this->userRepository->findOneBy(['email' => $login])) {
            $user = $this->userRepository->findOneBy(['email' => $login]);
        }
        {
            $user = new User();
        }


        $user->setStudent($student);

        $genPass = $this->globalHelpersService->gen_password(10);
        $password = $passwordHasher->hashPassword($user, $genPass);
        $student->setFirstPassword($genPass);
        $this->studentRepository->save($student);
        $user->setEmail($login . '@student.vatholm.ru');
        $user->setPassword($password);
        $user->setIsStudent(true);
        $this->userRepository->save($user, $flush);

        /**
         * Секция создания документов студента
         */
        $attestat = new PersonalDocuments();

        $attestat->setStudent($student);
        $attestatType = $this->personalDocTypeListRepository->findOneBy(['Name' => $abiturientPetition->getEducationDocumentName()]);
        $attestat->setDocumentType($attestatType);
        $attestat->setDocumentSeries('');
        $attestat->setDocumentNumber($abiturientPetition->getEducationDocumentNumber());
        $attestat->setComment('Средний балл: ' . $abiturientPetition->getEducationDocumentGPA());
        $attestat->setDocumentIssueDate(new DateTime('2001-01-01 00:00:00'));
        $this->personalDocumentsRepository->save($attestat, $flush);
        $abiturientPetition->setStatus($this->abiturientPetitionStatusRepository->findOneBy(['name' => 'INDUCTED']));
        $abiturientPetition->setIsBlockedToEdit(true);

        $this->abiturientPetitionRepository->save($abiturientPetition, $flush);

        /*
         * Секция извлечения параметров для переадресации
         */

        $admissionID = $abiturientPetition->getAdmission()->getId();
        $facutltyID = $abiturientPetition->getFaculty()->getId();
        $facutltyTitle = $abiturientPetition->getFaculty()->getName();

        /*
         * Переадресация в список рекомендованных к зачислению заявлений
         */

        return $this->redirectToRoute('app_abiturient_petition_show_by_admission_and_faculty_enroll',
            ['admissionID' => $admissionID,
                'facultyID' => $facutltyID,
                'facultyTitle' => $facutltyTitle],
            Response::HTTP_SEE_OTHER);
    }


    #[Route('/induct_index', name: 'app_abiturient_petition_show_by_admission_and_faculty_induct', methods: ['GET'])]
    #[IsGranted("ROLE_STAFF_AB_PETITIONS_R")]
    public function showinduct(Request $request, AbiturientPetitionRepository $abiturientPetitionRepository): Response
    {
        $petitionStatus = $this->abiturientPetitionStatusRepository->findOneBy(['name' => 'INDUCTED']);
        $admission = $this->admissionRepository->find($request->get('admissionID'));
        $facultyID = $this->facultyRepository->find($request->get('facultyID'));
        $abiturientPetitionList = $this->abiturientPetitionRepository->findBy(['admission' => $admission, 'Faculty' => $facultyID, 'documentObtained' => true, 'status' => $petitionStatus], ['educationDocumentGPA' => 'DESC']);
        $contingentDocuments = $this->contingentDocumentRepository->findBy(['isActive' => false]);
        return $this->render('abiturient_petition/index.html.twig', [
            'abiturient_petitions' => $abiturientPetitionList,
            'facultyTitle' => $request->get('facultyTitle'),
            'inducted' => true,
            'contingentDocumentsList' => $contingentDocuments,
        ]);
    }

    /**
     * @param AbiturientPetition $abiturientPetition
     * @param string $login
     * @return string
     */
    #[IsGranted("ROLE_STAFF_AB_PETITIONS_INDUCT")]
    private function checkUser($abiturientPetition, $login): string
    {
        $studentDomain = $abiturientPetition->getAdmission()->getCollege()->getSettingsStudentsDomain();
        $email = $login . '@' . $studentDomain;
        $user = $this->userRepository->findOneBy(['email' => $email]);
        $snils = (int)str_replace('-', '', str_replace(' ', '', $abiturientPetition->getDocumentSNILS()));
        if ($user and $snils == $user->getStudent()->getDocumentSnils()) {
            $result = $login;
        } else {
            $snilssubstr = mb_substr((string)$snils, 2, 3);

            $result = $login . '.' . $snilssubstr;

        }
        return $result;
    }
}