<?php

namespace App\Controller\Admission;

use App\Controller\IsGranted;
use App\Entity\AbiturientPetition;
use App\Form\AbiturientPetitionLoadType;
use App\Form\AbiturientPetitionType;
use App\Repository\AbiturientPetitionRepository;
use App\Repository\AbiturientPetitionStatusRepository;
use App\Repository\PersonaSexRepository;
use App\Service\Admission\PetitionLoadService;
use App\Service\Admission\PetitionToSiteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/admission/petition')]
class AbiturientPetitionController extends AbstractController
{
    public function __construct(
        private AbiturientPetitionStatusRepository $abiturientPetitionStatusRepository
    )
    {
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
    #[IsGranted("ROLE_STAFF_AB_PETITIONS_C")]
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
    public function edit(Request $request,PersonaSexRepository $personaSexRepository,AbiturientPetitionStatusRepository $abiturientPetitionStatusRepository, AbiturientPetition $abiturientPetition, AbiturientPetitionRepository $abiturientPetitionRepository): Response
    {
        $personaSexRepository->findAll();
        $abiturientPetitionStatusRepository->findAll();
        $form = $this->createForm(AbiturientPetitionType::class, $abiturientPetition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $abiturientPetitionRepository->save($abiturientPetition, true);

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

    #[Route('/uploadToSite', name: 'app_abiturient_petition_uploadToSite', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_STAFF_AB_PETITIONS_VIS")]
    public function uploadToSite(Request $request, AbiturientPetitionRepository $abiturientPetitionRepository, PetitionToSiteService $petitionToSiteService): Response
    {
        $petitionToSiteService->loadPetitionToSite();
        return $this->redirectToRoute('app_abiturient_petition_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/updatePetitionFromVIS/{GUID}', name: 'app_abiturient_petition_updateFromVIS', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_STAFF_AB_PETITIONS_VIS")]
    public function updatePetitionFromVIS(Request $request, AbiturientPetition $abiturientPetition, AbiturientPetitionRepository $abiturientPetitionRepository, petitionLoadService $petitionLoadService): Response
    {
        $petitionLoadService->pubUpdatePetitionAsObject($abiturientPetition);
        return $this->redirectToRoute('app_abiturient_petition_edit', ['id'=>$abiturientPetition->getId()], Response::HTTP_SEE_OTHER);
    }

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
}
