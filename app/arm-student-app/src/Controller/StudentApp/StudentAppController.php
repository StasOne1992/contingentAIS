<?php

namespace App\Controller\StudentApp;

use App\Entity\Student;
use App\Entity\User;
use App\Form\StudentAppProfile;
use App\Repository\StudentRepository;
use App\Service\StudentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function PHPUnit\Framework\isNull;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/student-app')]
#[IsGranted("ROLE_USER")]
class StudentAppController extends AbstractController
{
    private Student $Student;

    public function __construct(
        private StudentRepository $studentRepository,
        private StudentService $studentService,
    )
    {

       // $this->setStudent();
    }



    #[Route('/', name: 'app_student_dashboard', methods: ['GET'])]
    public function dashboad(StudentRepository $studentRepository): Response
    {
        //dd($this->getUser()->getUserIdentifier());
        $this->Student=$this->studentService->getStudentByUserId($this->getUser()->getUserIdentifier());

        return $this->render('student-app/dashboard.html.twig',
                [
                    'student' => $this->Student
                ]);
    }

    #[Route('/profile', name: 'app_student_profile', methods: ['GET'])]
    public function profile(StudentRepository $studentRepository): Response
    {
        $currentUser = $this->getUser()->getStudentProfileID();
        if ($currentUser != 0) {
            return $this->render('student-app/profile.html.twig',
                [
                    'student' => $studentRepository->findOneBy(array('id' => $currentUser)),

                ]);
        } else {
            return $this->render('student-app/error.html.twig');
        }


    }
}