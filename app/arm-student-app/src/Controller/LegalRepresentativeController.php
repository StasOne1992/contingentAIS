<?php

namespace App\Controller;

use App\Entity\LegalRepresentative;
use App\Form\LegalRepresentativeType;
use App\Repository\LegalRepresentativeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/leg_repres')]
class LegalRepresentativeController extends AbstractController
{
    #[Route('/', name: 'app_legal_representative_index', methods: ['GET'])]
    public function index(LegalRepresentativeRepository $legalRepresentativeRepository): Response
    {
        return $this->render('legal_representative/index.html.twig', [
            'legal_representatives' => $legalRepresentativeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_legal_representative_new', methods: ['GET', 'POST'])]
    public function new(Request $request, LegalRepresentativeRepository $legalRepresentativeRepository): Response
    {
        $legalRepresentative = new LegalRepresentative();
        $form = $this->createForm(LegalRepresentativeType::class, $legalRepresentative);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $legalRepresentativeRepository->save($legalRepresentative, true);

            return $this->redirectToRoute('app_legal_representative_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('legal_representative/new.html.twig', [
            'legal_representative' => $legalRepresentative,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_legal_representative_show', methods: ['GET'])]
    public function show(LegalRepresentative $legalRepresentative): Response
    {
        return $this->render('legal_representative/show.html.twig', [
            'legal_representative' => $legalRepresentative,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_legal_representative_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, LegalRepresentative $legalRepresentative, LegalRepresentativeRepository $legalRepresentativeRepository): Response
    {
        $form = $this->createForm(LegalRepresentativeType::class, $legalRepresentative);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $legalRepresentativeRepository->save($legalRepresentative, true);

            return $this->redirectToRoute('app_legal_representative_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('legal_representative/edit.html.twig', [
            'legal_representative' => $legalRepresentative,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_legal_representative_delete', methods: ['POST'])]
    public function delete(Request $request, LegalRepresentative $legalRepresentative, LegalRepresentativeRepository $legalRepresentativeRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$legalRepresentative->getId(), $request->request->get('_token'))) {
            $legalRepresentativeRepository->remove($legalRepresentative, true);
        }

        return $this->redirectToRoute('app_legal_representative_index', [], Response::HTTP_SEE_OTHER);
    }
}
