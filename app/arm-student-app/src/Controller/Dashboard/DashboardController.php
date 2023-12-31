<?php

namespace App\Controller\Dashboard;


use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\StudentGroupsRepository;
use App\Form\dashboard\DashboardType;
use App\Service\Messenger\BackgroudMessage;

#[Route('/dashboard')]
class DashboardController extends AbstractController
{
    public function __construct()
    {

    }

    #[Route('/', name: 'app_dashboard_index', methods: ['GET'])]
    public function index(StudentGroupsRepository $StudentGroupsRepository,UserRepository $UserRepository,BackgroudMessage $backgroudMessage): Response
    {

        $this->currentUser=$UserRepository->find($this->getUser());
        $this->groupLeaderId=$this->currentUser->getStaff()->getId();
        $this->groups=$StudentGroupsRepository->findby(['GroupLeader'=>$this->groupLeaderId]);

        //$backgroudMessage->push('toast-notify', 'success', ' fa fa-check me-1 ', 'БЛОКИРОВКА. Заявление не доступно для изменения', "2123124124");
        return $this->render('dashboard/index.html.twig',
            [
                'groups'=>$this->groups
            ]);
    }

}