<?php

namespace App\Service;

use App\Entity\Student;
use App\Entity\User;
use App\Repository\StudentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use function PHPUnit\Framework\isNull;


class StudentService
{
    public function __construct(
        private StudentRepository $studentRepository,
        private UserRepository    $userRepository,
    )
    {
    }

    public function fillStudentsUUID()
    {

        $studentsList = $this->studentRepository->findBy(['UUID' => null]);
        foreach ($studentsList as $student) {
            $this->setStudentUUID($student);
        }
    }

    /**
     * @param Student $student
     * @return void
     */
    public function setStudentUUID($student): void
    {
        $student->setUUID(uniqid());
        $this->studentRepository->save($student, true);
    }


    public function fillStudentsEmails()
    {
        $studentsList = $this->studentRepository->findBy(['email' => null]);
        foreach ($studentsList as $student) {
            $this->setStudentEmailByUser($student);
        }
    }

    /**
     * @param Student $student
     * @return void
     */
    public function setStudentEmailByUser($student): void
    {
        $user = $this->userRepository->findOneBy(['Student' => $student->getId()]);
        if($user!=null) {
            if ($user->getEmail() != null) {
                $student->setEmail($user->getEmail());
                $this->studentRepository->save($student,true);
                }
        }
    }
}