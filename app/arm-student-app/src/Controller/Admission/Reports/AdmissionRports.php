<?php

namespace App\Controller\Admission\Reports;

use App\Repository\AbiturientPetitionRepository;
use App\Repository\AbiturientPetitionStatusRepository;
use App\Service\TypicalDocuments;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admission/reports')]
class AdmissionRports extends AbstractController
{

    public function __construct(
        private AbiturientPetitionRepository $abiturientPetitionRepository,
        private AbiturientPetitionStatusRepository $abiturientPetitionStatusRepository,
        private TypicalDocuments $typicalDocuments
    )
    {
    }



}