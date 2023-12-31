<?php

namespace App\Controller;

use App\Entity\LoginProvider;
use App\Form\LoginProviderType;
use App\Repository\LoginProviderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/login/provider')]
class LoginProviderController extends AbstractController
{
    #[Route('/', name: 'app_login_provider_index', methods: ['GET'])]
    public function index(LoginProviderRepository $loginProviderRepository): Response
    {
        return $this->render('login_provider/index.html.twig', [
            'login_providers' => $loginProviderRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_login_provider_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $loginProvider = new LoginProvider();
        $form = $this->createForm(LoginProviderType::class, $loginProvider);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($loginProvider);
            $entityManager->flush();

            return $this->redirectToRoute('app_login_provider_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('login_provider/new.html.twig', [
            'login_provider' => $loginProvider,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_login_provider_show', methods: ['GET'])]
    public function show(LoginProvider $loginProvider): Response
    {
        return $this->render('login_provider/show.html.twig', [
            'login_provider' => $loginProvider,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_login_provider_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, LoginProvider $loginProvider, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LoginProviderType::class, $loginProvider);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_login_provider_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('login_provider/edit.html.twig', [
            'login_provider' => $loginProvider,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_login_provider_delete', methods: ['POST'])]
    public function delete(Request $request, LoginProvider $loginProvider, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$loginProvider->getId(), $request->request->get('_token'))) {
            $entityManager->remove($loginProvider);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_login_provider_index', [], Response::HTTP_SEE_OTHER);
    }
}
