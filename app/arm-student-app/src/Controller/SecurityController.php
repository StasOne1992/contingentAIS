<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/accessDenied', name: 'app_access_denied')]
    public function accesDenied():Response
    {
        return $this->render('security/accessDenied.html.twig');
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/secure', name: 'app_secure')]
    public function indexAction()
    {
        if ($this->isGranted('ROLE_ROOT')) {
            return $this->redirectToRoute('app_dashboard_index');
        }
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('app_dashboard_index');
        }
        if ($this->isGranted('ROLE_ADM')) {
            return $this->redirectToRoute('app_dashboard_index');
        }

        if ($this->isGranted('ROLE_CL')) {
            return $this->redirectToRoute('app_dashboard_index');
        }
        if ($this->isGranted('ROLE_STUDENT')) {
            return $this->redirectToRoute('app_student_dashboard');
        }
        throw new \Exception(AccessDeniedException::class);
    }

}
