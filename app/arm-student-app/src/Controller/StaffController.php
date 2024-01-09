<?php

namespace App\Controller;

use App\Entity\Staff;
use App\Entity\User;
use App\Form\StaffType;
use App\Repository\CollegeRepository;
use App\Repository\StaffRepository;
use App\Repository\UserRepository;
use App\Service\GlobalHelpersService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/staff')]
#[IsGranted("ROLE_USER")]
class StaffController extends AbstractController
{
    #[Route('/', name: 'app_staff_index', methods: ['GET'])]
    public function index(StaffRepository $staffRepository): Response
    {
        return $this->render('staff/index.html.twig', [
            'staff' => $staffRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_staff_new', methods: ['GET', 'POST'])]
    public function new(Request $request, StaffRepository $staffRepository): Response
    {
        $staff = new Staff();
        $staff->setUUID(uniqid());
        $form = $this->createForm(StaffType::class, $staff);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $staffRepository->save($staff, true);

            return $this->redirectToRoute('app_staff_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('staff/new.html.twig', [
            'staff' => $staff,
            'form' => $form,
        ]);
    }

    #[Route('/fillusers', name: 'app_staff_fillusers', methods: ['GET', 'POST'])]
    public function fillusers(Request $request, StaffRepository $staffRepository): Response
    {
        if ($staffRepository->findAll()) {
            /***
             * @var Staff $staff
             */
            foreach ($staffRepository->findAll() as $staff) {

                if ($staff->getEmail() == null) {
                    if ($staff->getUsers()->getValues()) {

                        $user = $staff->getUsers()->getValues();
                        /**
                         * @var User $user
                         */
                        $staff->setEmail($user[0]->getEmail());
                    }
                }
                if ($staff->getUUID() == null) {
                    $staff->setUUID(uniqid());
                }

                $staffRepository->save($staff, true);

            }
        }
        return $this->redirectToRoute('app_staff_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/createUsers', name: 'app_staff_createusers', methods: ['GET', 'POST'])]
    public function createusers(Request $request, StaffRepository $staffRepository, UserRepository $userRepository,GlobalHelpersService $globalHelpersService, UserPasswordHasherInterface $passwordHasher, CollegeRepository $collegeRepository): Response
    {
        if ($staffRepository->findAll()) {
            /***
             * @var Staff $staff
             */
            foreach ($staffRepository->findAll() as $staff) {

                if ($staff->getUsers()->getValues() == null) {
                    dump($staff);
                    $user = New User();
                    $college=$collegeRepository->find(1);
                    $user->setEmail($globalHelpersService->translit($staff->getLastName().$staff->getFirstName()[1].'@'.$college->getSettingsStaffDomain()));
                    $genPass = $globalHelpersService->gen_password(10);
                    $password = $passwordHasher->hashPassword($user, $genPass);
                    $user->setPassword($password);
                    dump($user);


                }
            }
            dd('');
        }

        return $this->redirectToRoute('app_staff_index', [], Response::HTTP_SEE_OTHER);
    }

    #[
        Route('/{id}/show', name: 'app_staff_show', methods: ['GET'])]
    public function show(Staff $staff): Response
    {
        return $this->render('staff/show.html.twig', [
            'staff' => $staff,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_staff_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Staff $staff, StaffRepository $staffRepository): Response
    {
        $form = $this->createForm(StaffType::class, $staff);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $staffRepository->save($staff, true);

            return $this->redirectToRoute('app_staff_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('staff/edit.html.twig', [
            'staff' => $staff,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_staff_delete', methods: ['POST'])]
    public function delete(Request $request, Staff $staff, StaffRepository $staffRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $staff->getId(), $request->request->get('_token'))) {
            $staffRepository->remove($staff, true);
        }

        return $this->redirectToRoute('app_staff_index', [], Response::HTTP_SEE_OTHER);
    }
}
