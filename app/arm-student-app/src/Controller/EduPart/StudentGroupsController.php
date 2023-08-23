<?php

namespace App\Controller\EduPart;

use App\Entity\StudentGroups;
use App\Form\StudentGroupsType;
use App\Repository\StudentGroupsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\StudentGroupsService;


#[Route('/student-groups')]
class StudentGroupsController extends AbstractController
{
    #[Route('/', name: 'app_student_groups_index', methods: ['GET'])]
    public function index(StudentGroupsRepository $studentGroupsRepository): Response
    {
        $user = $this->getUser();
        if (in_array('ROLE_CL', $user->getRoles()) and !in_array('ROLE_ROOT', $user->getRoles())) {
            dump('haverole CL');
        }
        else if (in_array('ROLE_ROOT', $user->getRoles()))
        {
            dump('RoleRoot');
        }

        return $this->render('student_groups/index.html.twig', [
            'student_groups' => $studentGroupsRepository->findAll(),
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
        $studentGroup->getStudents()->getValues();
        $studentGroup->socialPassport = $StudentGroupsService->generateSocialPasport($studentGroup);


        return $this->render('student_groups/show.html.twig', [
            'student_group' => $studentGroup,
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
}
