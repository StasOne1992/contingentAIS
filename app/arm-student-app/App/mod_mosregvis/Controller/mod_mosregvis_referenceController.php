<?php

namespace App\mod_mosregvis\Controller;


use App\MainApp\Repository\ModMosregVisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/mod_mosregvis/reference')]
#[IsGranted("ROLE_USER")]
class mod_mosregvis_referenceController extends AbstractController
{
    #[Route('/getFromVis', name: 'mod_mosregvis_reference_update_reference', methods: ['POST','GET'])]
    public function updateReference(ModMosregVisRepository $modMosregVisRepository): Response
    {
        return $this->render('@mod_mosregvis/index.html.twig',
            [
                'modMosregVis'=>$modMosregVisRepository->findAll()
            ]
        );
    }
}