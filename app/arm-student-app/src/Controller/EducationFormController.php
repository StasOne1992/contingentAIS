<?php

namespace App\Controller;

use App\Entity\EducationForm;
use App\Form\EducationFormType;
use App\Repository\EducationFormRepository;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/education_form')]
#[IsGranted("ROLE_USER")]
class EducationFormController extends AbstractController
{
    #[Route('/', name: 'app_education_form_index', methods: ['GET'])]
    public function index(EducationFormRepository $educationFormRepository): Response
    {
        return $this->render('education_form/index.html.twig', [
            'education_forms' => $educationFormRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_education_form_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EducationFormRepository $educationFormRepository): Response
    {
        $educationForm = new EducationForm();
        $form = $this->createForm(EducationFormType::class, $educationForm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $educationFormRepository->save($educationForm, true);

            return $this->redirectToRoute('app_education_form_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('education_form/new.html.twig', [
            'education_form' => $educationForm,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_education_form_show', methods: ['GET'])]
    public function show(EducationForm $educationForm): Response
    {
        return $this->render('education_form/show.html.twig', [
            'education_form' => $educationForm,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_education_form_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EducationForm $educationForm, EducationFormRepository $educationFormRepository): Response
    {
        $form = $this->createForm(EducationFormType::class, $educationForm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $educationFormRepository->save($educationForm, true);

            return $this->redirectToRoute('app_education_form_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('education_form/edit.html.twig', [
            'education_form' => $educationForm,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_education_form_delete', methods: ['POST'])]
    public function delete(Request $request, EducationForm $educationForm, EducationFormRepository $educationFormRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$educationForm->getId(), $request->request->get('_token'))) {
            $educationFormRepository->remove($educationForm, true);
        }

        return $this->redirectToRoute('app_education_form_index', [], Response::HTTP_SEE_OTHER);
    }
}
