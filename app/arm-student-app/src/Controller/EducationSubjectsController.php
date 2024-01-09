<?php

namespace App\Controller;

use App\Entity\EducationSubjects;
use App\Form\EducationSubjectsType;
use App\Repository\EducationSubjectsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/education/subjects')]
#[IsGranted("ROLE_USER")]
class EducationSubjectsController extends AbstractController
{
    #[Route('/', name: 'app_education_subjects_index', methods: ['GET'])]
    public function index(EducationSubjectsRepository $educationSubjectsRepository): Response
    {
        return $this->render('education_subjects/index.html.twig', [
            'education_subjects' => $educationSubjectsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_education_subjects_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $educationSubject = new EducationSubjects();
        $form = $this->createForm(EducationSubjectsType::class, $educationSubject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($educationSubject);
            $entityManager->flush();

            return $this->redirectToRoute('app_education_subjects_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('education_subjects/new.html.twig', [
            'education_subject' => $educationSubject,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_education_subjects_show', methods: ['GET'])]
    public function show(EducationSubjects $educationSubject): Response
    {
        return $this->render('education_subjects/show.html.twig', [
            'education_subject' => $educationSubject,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_education_subjects_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EducationSubjects $educationSubject, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EducationSubjectsType::class, $educationSubject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_education_subjects_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('education_subjects/edit.html.twig', [
            'education_subject' => $educationSubject,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_education_subjects_delete', methods: ['POST'])]
    public function delete(Request $request, EducationSubjects $educationSubject, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$educationSubject->getId(), $request->request->get('_token'))) {
            $entityManager->remove($educationSubject);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_education_subjects_index', [], Response::HTTP_SEE_OTHER);
    }
}
