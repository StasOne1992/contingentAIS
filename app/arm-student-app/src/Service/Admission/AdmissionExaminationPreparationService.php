<?php

namespace App\Service\Admission;

use App\Entity\AbiturientPetition;
use App\Entity\AdmissionExamination;
use App\Entity\AdmissionExaminationResult;
use App\Entity\AdmissionPlan;
use App\Repository\AbiturientPetitionRepository;
use App\Repository\AdmissionExaminationRepository;
use App\Repository\AdmissionExaminationResultRepository;
use App\Repository\AdmissionExaminationSubjectsRepository;
use App\Repository\AdmissionPlanRepository;
use App\Repository\AdmissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use http\Env\Response;

/**
 * Service to prepair petition to Examination
 */
class AdmissionExaminationPreparationService
{

    public function __construct(
        private AbiturientPetitionRepository           $abiturientPetitionRepository,
        private AdmissionExaminationResultRepository   $admissionExaminationResultRepository,
        private AdmissionExaminationRepository         $admissionExaminationRepository,
        private AdmissionRepository                    $admissionRepository,
        private AdmissionExaminationSubjectsRepository $admissionExaminationSubjectsRepository,
        private AdmissionPlanRepository                $admissionPlanRepository,
        private EntityManagerInterface                 $entityManager,
    )
    {
        $this->admissionExaminationRepository->findAll();
        $this->admissionExaminationResultRepository->findAll();
        $this->admissionExaminationSubjectsRepository->findAll();
        $this->admissionPlanRepository->findAll();
    }

    /**
     * Публичная функция, которая выполняет получение списка заявлений,
     * выполняет подготовку к созданию ведомостей экзаменов
     * @param $PetitionList
     * @return void
     */
    public function AdmissionExaminationPreparationPrepare($PetitionList,$AdmissionExamination): void
    {
        /**
         * @var AbiturientPetition $Petition
         */
        foreach ($PetitionList as $Petition) {
            dump('START ' . $Petition->getNumber());
            dump($Petition->getAdmissionPlanPosition()->getAdmissionExaminations()->getValues());
                dump($AdmissionExamination);
                dump('START RESULT');
                if ($Petition->getResult()->count() != 0 or $Petition->getResult()->count() != null) {
                    if (!$this->admissionExaminationResultRepository->findOneBy(['AbiturientPetition' => $Petition, 'AdmissionExamination' => $AdmissionExamination])) {
                        dump('No current Admission Examination row');
                        $ExaminationResult = new AdmissionExaminationResult();
                        $ExaminationResult->setAdmissionExamination($AdmissionExamination);
                        $ExaminationResult->setAbiturientPetition($Petition);
                        $ExaminationResult->setMark(0);
                        $this->entityManager->persist($ExaminationResult);
                        $this->entityManager->flush();
                        dump('Created Examination Result Row: ' . $ExaminationResult->getId());
                    }

                } else {
                    dump('Not Result Found');
                    $ExaminationResult = new AdmissionExaminationResult();
                    $ExaminationResult->setAdmissionExamination($AdmissionExamination);
                    $ExaminationResult->setAbiturientPetition($Petition);
                    $ExaminationResult->setMark(0);
                    $this->entityManager->persist($ExaminationResult);
                    $this->entityManager->flush();
                    dump('Created Examination Result Row: ' . $ExaminationResult->getId());
                }
                dump('END RESULT');

            dump('END ' . $Petition->getNumber());
        }
    }
}