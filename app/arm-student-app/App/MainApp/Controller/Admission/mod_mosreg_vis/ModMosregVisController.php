<?php

namespace App\MainApp\Controller\Admission\mod_mosreg_vis;

use App\MainApp\Entity\ModMosregVis;
use App\MainApp\Form\ModMosregVisType;
use App\MainApp\Repository\ModMosregVisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/mod/mosreg/vis')]
class ModMosregVisController extends AbstractController
{
    #[Route('/', name: 'app_mod_mosreg_vis_index', methods: ['GET'])]
    public function index(ModMosregVisRepository $modMosregVisRepository): Response
    {
        return $this->render('mod_mosreg_vis/index.html.twig', [
            'mod_mosreg_vis' => $modMosregVisRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_mod_mosreg_vis_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $modMosregVi = new ModMosregVis();
        $form = $this->createForm(ModMosregVisType::class, $modMosregVi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($modMosregVi);
            $entityManager->flush();

            return $this->redirectToRoute('app_mod_mosreg_vis_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('mod_mosreg_vis/new.html.twig', [
            'mod_mosreg_vi' => $modMosregVi,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_mod_mosreg_vis_show', methods: ['GET'])]
    public function show(ModMosregVis $modMosregVi): Response
    {
        return $this->render('mod_mosreg_vis/show.html.twig', [
            'mod_mosreg_vi' => $modMosregVi,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_mod_mosreg_vis_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ModMosregVis $modMosregVi, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ModMosregVisType::class, $modMosregVi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_mod_mosreg_vis_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('mod_mosreg_vis/edit.html.twig', [
            'mod_mosreg_vi' => $modMosregVi,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_mod_mosreg_vis_delete', methods: ['POST'])]
    public function delete(Request $request, ModMosregVis $modMosregVi, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$modMosregVi->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($modMosregVi);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_mod_mosreg_vis_index', [], Response::HTTP_SEE_OTHER);
    }
}
