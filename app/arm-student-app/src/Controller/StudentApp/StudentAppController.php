<?php

namespace App\Controller\StudentApp;

use App\Form\StudentAppProfile;
use App\Repository\StudentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/student-app')]
class StudentAppController extends AbstractController
{
    #[Route('/', name: 'app_student_dashboard', methods: ['GET'])]
    public function dashboad(StudentRepository $studentRepository): Response
    {
        $currentUser = $this->getUser()->getStudentProfileID();
        if ($currentUser != 0) {
            return $this->render('student-app/dashboard.html.twig',
                [
                    'student' => $studentRepository->findOneBy(array('id' => $currentUser)),

                ]);
        } else {
            return $this->render('student-app/error.html.twig');
        }
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