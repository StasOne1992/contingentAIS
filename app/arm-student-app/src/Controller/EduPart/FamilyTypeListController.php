<?php

namespace App\Controller\EduPart;

use App\Entity\FamilyTypeList;
use App\Form\FamilyTypeListType;
use App\Repository\FamilyTypeListRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/family/TypeList')]
class FamilyTypeListController extends AbstractController
{
    #[Route('/', name: 'app_family_type_list_index', methods: ['GET'])]
    public function index(FamilyTypeListRepository $familyTypeListRepository): Response
    {
        return $this->render('family_type_list/index.html.twig', [
            'family_type_lists' => $familyTypeListRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_family_type_list_new', methods: ['GET', 'POST'])]
    public function new(Request $request, FamilyTypeListRepository $familyTypeListRepository): Response
    {
        $familyTypeList = new FamilyTypeList();
        $form = $this->createForm(FamilyTypeListType::class, $familyTypeList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $familyTypeListRepository->save($familyTypeList, true);

            return $this->redirectToRoute('app_family_type_list_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('family_type_list/new.html.twig', [
            'family_type_list' => $familyTypeList,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_family_type_list_show', methods: ['GET'])]
    public function show(FamilyTypeList $familyTypeList): Response
    {
        return $this->render('family_type_list/show.html.twig', [
            'family_type_list' => $familyTypeList,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_family_type_list_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FamilyTypeList $familyTypeList, FamilyTypeListRepository $familyTypeListRepository): Response
    {
        $form = $this->createForm(FamilyTypeListType::class, $familyTypeList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $familyTypeListRepository->save($familyTypeList, true);

            return $this->redirectToRoute('app_family_type_list_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('family_type_list/edit.html.twig', [
            'family_type_list' => $familyTypeList,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_family_type_list_delete', methods: ['POST'])]
    public function delete(Request $request, FamilyTypeList $familyTypeList, FamilyTypeListRepository $familyTypeListRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$familyTypeList->getId(), $request->request->get('_token'))) {
            $familyTypeListRepository->remove($familyTypeList, true);
        }

        return $this->redirectToRoute('app_family_type_list_index', [], Response::HTTP_SEE_OTHER);
    }
}
