<?php

namespace App\Controller\EduPart;

use App\Entity\College;
use App\Entity\ContingentDocument;
use App\Form\ContingentDocumentType;
use App\Repository\ContingentDocumentRepository;
use App\Repository\StudentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\TypicalDocuments;
use App\Repository\CollegeRepository;

#[Route('/contingent/document')]
class ContingentDocumentController extends AbstractController
{
    #[Route('/', name: 'app_contingent_document_index', methods: ['GET'])]
    public function index(ContingentDocumentRepository $contingentDocumentRepository): Response
    {
        return $this->render('contingent_document/index.html.twig', [
            'contingent_documents' => $contingentDocumentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_contingent_document_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ContingentDocumentRepository $contingentDocumentRepository): Response
    {
        $contingentDocument = new ContingentDocument();
        $form = $this->createForm(ContingentDocumentType::class, $contingentDocument);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contingentDocumentRepository->save($contingentDocument, true);

            return $this->redirectToRoute('app_contingent_document_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('contingent_document/new.html.twig', [
            'contingent_document' => $contingentDocument,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contingent_document_show', methods: ['GET'])]
    public function show(ContingentDocument $contingentDocument, ContingentDocumentRepository $contingentDocumentRepository): Response
    {
        $contingentDocument->getStudent()->getValues();
        return $this->render('contingent_document/show.html.twig', [
            'contingent_document' => $contingentDocument,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contingent_document_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ContingentDocument $contingentDocument, ContingentDocumentRepository $contingentDocumentRepository): Response
    {
        $form = $this->createForm(ContingentDocumentType::class, $contingentDocument);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //dd($form->getData());
            $contingentDocumentRepository->save($contingentDocument, true);

            return $this->redirectToRoute('app_contingent_document_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('contingent_document/edit.html.twig', [
            'contingent_document' => $contingentDocument,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contingent_document_delete', methods: ['POST'])]
    public function delete(Request $request, ContingentDocument $contingentDocument, ContingentDocumentRepository $contingentDocumentRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $contingentDocument->getId(), $request->request->get('_token'))) {
            $contingentDocumentRepository->remove($contingentDocument, true);
        }

        return $this->redirectToRoute('app_contingent_document_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}/print', name: 'app_contingent_document_print')]
    public function print(Request $request, ContingentDocument $contingentDocument, ContingentDocumentRepository $contingentDocumentRepository, TypicalDocuments $typicalDocuments, CollegeRepository $collegeRepository){
        $contingentDocument->getStudent()->getValues();
        $collegeRepository->findBy(['id'=>$contingentDocument->getCollege()->getId()]);



        $contingentDocument=$typicalDocuments->generateOrder($contingentDocument);

        return $this->renderForm('_printtemplate.html.twig', [
            'content' => $contingentDocument,
        ]);
    }

    #[Route ('/setsutdents/{id}', name: 'app_contingent_document_set', methods: ['GET', 'POST'])]
    public function setstudent(ContingentDocumentRepository $contingentDocumentRepository, StudentRepository $studentRepository)
    {
        $contingentDocumentId = $_POST['contingent_document_id'];
        $contingentDocument = $contingentDocumentRepository->findOneBy(['id' => $contingentDocumentId]);


        foreach ($_POST as $item => $value) {
            if (str_starts_with($item,'row')) {
                $student=$studentRepository->findOneBy(['id'=>$value]);
                $contingentDocument->addStudent($student);
            }
        }
        $contingentDocumentRepository->save($contingentDocument,true);
        return $this->redirectToRoute('app_contingent_document_edit', ['id'=>$contingentDocumentId], Response::HTTP_SEE_OTHER);
    }

}
