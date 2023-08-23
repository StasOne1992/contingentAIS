<?php

namespace App\Service\Admission;


use App\Entity\AbiturientPetition;
use App\Entity\AbiturientPetitionStatus;
use App\Entity\AdmissionPlan;
use App\Message\PetitionLoadMessage;
use App\Message\PetitionNewLoadMessage;
use App\Message\Stamp\AnotherStamp;
use App\Repository\AbiturientPetitionRepository;
use App\Repository\AbiturientPetitionStatusRepository;
use App\Repository\AdmissionPlanRepository;
use App\Repository\AdmissionRepository;
use App\Repository\AdmissionStatusRepository;
use App\Repository\EducationFormRepository;
use App\Repository\EducationTypeRepository;
use App\Repository\FacultyRepository;
use App\Repository\FinancialTypeRepository;
use App\Repository\PersonaSexRepository;
use App\Repository\SpecializationRepository;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PetitionLoadService
{

    ###
    ### Конструктор класса
    ### $client интерфейс взаимодействия с HTTP
    ### $Token  API доступа к ВИС
    ###

    public function __construct
    (
        private HttpClientInterface                $client,
        private AbiturientPetitionRepository       $petitionRepository,
        private PersonaSexRepository               $personaGenderRpository,
        private FacultyRepository                  $facultyRepository,
        private AbiturientPetitionStatusRepository $abiturientPetitionStatusRepository,
        private AbiturientPetitionRepository       $abiturientPetitionRepository,
        private AdmissionRepository                $admissionRepository,
        private AdmissionStatusRepository          $admissionStatusRepository,
        private EducationTypeRepository            $educationTypeRepository,
        private FinancialTypeRepository            $financialTypeRepository,
        private EducationFormRepository            $educationFormRepository,
        private SpecializationRepository           $specializationRepository,
        private HubInterface                       $hub,
        private MessageBusInterface                $bus,
        private AdmissionPlanRepository            $admissionPlanRepository,
        private string                             $Token = '',

    )
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
        $this->setAuthToken();
        $this->checkAuth();
    }


    ###
    ### Блок взаимодействия с ВИС Зачисление в ПОО Московская область
    ###
    ### getAuthToken() - Получает токен для авторизации последующих запросов и устанавливает его значение в поле $Token

    private
    function getAuthToken(): string
    {
        $authinfo['username'] = 'sergachovsv';
        $authinfo['password'] = '3e605cd3bedf0aad5463a6bc3345a2d436294ec861d9962f531c004e4ec73508';
        $authinfo = json_encode($authinfo, JSON_THROW_ON_ERROR);
        $response = $this->client->request('POST', 'http://prof.mo.mosreg.ru/api/login',
            [
                'headers' => ['Accept: */*', 'Content-Type: application/json', 'Cookie: Cookie_1=value'],
                'body' => $authinfo
            ]
        );
        if (200 !== $response->getStatusCode()) {

            throw new Exception('Ошибка получения authToken. Код ошибки:  ' . $response->getStatusCode());
        }
        $responseJson = $response->getContent();
        $responseData = json_decode($responseJson, true, 512, JSON_THROW_ON_ERROR);
        return $responseData['token'];
    }

    private function setAuthToken(): void
    {
        $this->Token = $this->getAuthToken();
    }

    private function checkAuth()
    {
        $response = $this->client->request('GET', 'http://prof.mo.mosreg.ru/api/spoPetition/search/advancedSearch?page=0&size=5000&order=asc&projection=grid&q=%7B%22spoEducationYear%22%3A%225%22%7D',
            ['headers' => ['Authorization: Token ' . $this->Token]]);
        if (401 === $response->getStatusCode()) {
            $this->setAuthToken();
        }
    }

    private
    function getPetitionList(): array
    {

        $response = $this->client->request('GET', 'http://prof.mo.mosreg.ru/api/spoPetition/search/advancedSearch?page=0&size=5000&order=asc&projection=grid&q=%7B%22spoEducationYear%22%3A%225%22%7D',
            ['headers' => ['Authorization: Token ' . $this->Token]]);
        if (200 !== $response->getStatusCode()) {

            throw new Exception('Ошибка получения списка заявлений. Код ошибки:' . $response->getStatusCode());
        }

        $responseJson = $response->getContent();
        $responseData = json_decode($responseJson, true, 512, JSON_THROW_ON_ERROR);

        if (count($responseData['_embedded']['spoPetitions']) > 0) {
            return $responseData['_embedded']['spoPetitions'];
        }
        return array();
    }
    ###
    ### Конец блока взаимодействия с ВИС Зачисление в ПОО Московская область
    ###


    public
    function getPetitionListToUpdate(): array
    {
        $responseData = $this->getPetitionList();
        $notLoad = array();

        if (count($responseData) > 0) {
            $responseDataIdColumnArray = array_column($responseData, 'id');
            $petitionRepoData = $this->petitionRepository->getAllGUID();

            foreach ($responseData as $petition) {
                if (array_search($petition['id'], $petitionRepoData) === false) {
                    $notLoad[] = $responseData[array_search($petition['id'], $responseDataIdColumnArray)];
                    unset($responseData[array_search($petition['id'], $responseDataIdColumnArray)]);
                };
            }
            return $responseData;
        }
        return array();
    }

    public
    function getNewPetitionList(): array
    {
        $petitionRepoData = $this->petitionRepository->getAllGUID();

        $spoPetitions = $this->getPetitionList();

        $spoPetitionsGUID = array_column($spoPetitions, 'id');
        $diff = array_diff($spoPetitionsGUID, $petitionRepoData);
        $toLoad = array();
        foreach ($diff as $petition) {
            foreach ($spoPetitions as $spoPetition) {
                if ($petition == $spoPetition['id']) {
                    $toLoad[] = $spoPetition;
                }
            }
        }

        return $toLoad;
    }

    public
    function submitPetitionList($petitionList): void
    {
        $counter = 1;
        foreach ($petitionList as $petition) {
            if (!is_null($this->petitionRepository->findOneBy(['GUID' => $petition['id']]))) {
                unset($petitionList[$petition['id']]);
            } else {
                $envelopeContent['petition'] = $petition;
                $envelopeContent['currentElement'] = $counter;
                $envelopeContent['countElements'] = count($petitionList);
                /*$envelope = $this->bus->dispatch(new PetitionNewLoadMessage($envelopeContent), [
                    new DelayStamp(500)
                ]);*/

                $envelope = $this->bus->dispatch(new PetitionNewLoadMessage($petition), [
                    new DelayStamp(500)
                ]);
            };
        }

    }

    public
    function updatePetitionList($petitionList): array
    {
        $handledResult = array();
        $counter = 1;
        foreach ($petitionList as $petition) {
            if (!is_null($this->petitionRepository->findOneBy(['GUID' => $petition['id']]))) {
                $envelopeContent['petition'] = $petition;
                $envelopeContent['currentElement'] = $counter;
                $envelopeContent['countElements'] = count($petitionList);

                /*$envelope = $this->bus->dispatch(new PetitionLoadMessage($envelopeContent), [
                    new DelayStamp(500)
                ]);*/

                $envelope = $this->bus->dispatch(new PetitionLoadMessage($petition), [
                    new DelayStamp(500)
                ]);
                $handledStamp = $envelope->last(HandledStamp::class);
                $handledResult[] = $handledStamp;


            } else {
                unset($petitionList[$petition['id']]);
            };
            $counter = $counter + 1;
        }
        return $handledResult;
    }


    public
    function getDetailsPetiton($petitionId): array
    {
        $Token = $this->Token;
        if (is_null($Token)) {
            $Token = $this->getAuthToken();
        }
        $Token = $this->getAuthToken();

        $response = $this->client->request('GET', 'http://prof.mo.mosreg.ru/api/spoPetition/' . $petitionId . '?projection=detail',
            ['headers' => ['Authorization: Token ' . $Token]]);
        if (200 !== $response->getStatusCode()) {

            throw new Exception('Ошибка получения подробностей для заявления ' . $petitionId . 'inSystemAuthToken:' . $this->Token . ' AuthToken: ' . $Token . ' Код ошибки: ' . $response->getStatusCode());
        }
        $responseJson = $response->getContent();
        $responseData = json_decode($responseJson, true, 512, JSON_THROW_ON_ERROR);

        return $responseData;
    }

    public
    function pubUpdatePetition($PetitionData): void
    {
        $this->updatePetition($PetitionData);
    }


    public
    function pubUpdatePetitionAsObject($PetitionObj): void
    {
        $this->updatePetitionAsObject($PetitionObj);
    }

    private
    function updatePetitionAsObject($PetitionObj): void
    {
        $petition = $PetitionObj;
        $this->fillPetition($petition, $PetitionObj);
        if (!$petition->isLockUpdateFormVIS()) {
            $petition->setLastUpdateTS(new DateTime(date("Y-m-d H:i:s")));
            $this->petitionRepository->save($petition, true);
            $this->push('popup-notify', 'success', ' fa fa-check me-1 ', 'Обновлено заявление', $petition['number']());
        } else {
            $this->push('popup-notify', 'success', ' fa fa-check me-1 ', 'БЛОКИРОВКА. Заявление не доступно для изменения', $petition->getNumber());
        }
    }


    private
    function updatePetition($PetitionData): void
    {
        if (is_array($PetitionData)) {
            $petition = $this->petitionRepository->findOneBy(['GUID' => $PetitionData['id']]);
        } else {
            $petition = $PetitionData;
        }


        $this->fillPetition($petition, $PetitionData);
        $petition->setLastUpdateTS(new DateTime(date("Y-m-d H:i:s")));
        if (!$petition->isLockUpdateFormVIS()) {
            $this->petitionRepository->save($petition, true);
            $this->push('popup-notify', 'success', ' fa fa-check me-1 ', 'Обновлено заявление', $petition->getNumber());
        } else {
            $this->push('popup-notify', 'success', ' fa fa-check me-1 ', 'БЛОКИРОВКА. Заявление не доступно для изменения', $petition->getNumber());
        }
    }

    public
    function pubCreatePetition($PetitionData): void
    {
        //dd($PetitionData);
        $petition = $this->createPetition($PetitionData);
        $this->push('popup-notify', 'success', ' fa fa-check me-1 ', 'Создано заявление', $petition->getNumber());
    }

    public
    function pubCreatePetitionWithNotify($PetitionData): void
    {
        //dd($PetitionData);
        $petition = $this->createPetition($PetitionData);
        $this->push('popup-notify', 'success', ' fa fa-check me-1 ', 'Создано заявление', $petition->getNumber());
    }

    private
    function createPetition($PetitionData)
    {
        if (!$this->abiturientPetitionRepository->findOneBy(['GUID' => $PetitionData['id']])) {
            $petition = new AbiturientPetition();
            $this->fillPetition($petition, $PetitionData);
            $petition->setUploadTS(new DateTime(date("Y-m-d H:i:s")));
            $this->petitionRepository->save($petition, true);
            return $petition;
        }
        return array();

    }

    private
    function fillPetition($petition, $PetitionData): AbiturientPetition
    {

        if (is_array($PetitionData)) {
            $petition->setGUID($PetitionData['id']);
            $PetitionData['details'] = $this->getDetailsPetiton($PetitionData['id']);
        } else {
            $GUID = $PetitionData->getGUID();
            $PetitionData = array();
            $PetitionData['details'] = $this->getDetailsPetiton($GUID);
        }
        /***
         * @var AbiturientPetition $petition
         */
        $petition->setFirstName($PetitionData['details']['document']['firstName']);
        $petition->setLastName($PetitionData['details']['document']['lastName']);
        $petition->setMiddleName($PetitionData['details']['document']['middleName']);
        $petition->setDocumentSNILS($PetitionData['details']['snils']);
        $petition->setNumber($PetitionData['details']['number']);
        $petition->setGender($this->convertGender($PetitionData['details']['gender']));
        $petition->setPhone($this->convertPhone($PetitionData['details']['phone']));
        $petition->setRegistrationAddress($PetitionData['details']['registration']['formatted']);
        if (isset($PetitionData['details']['currentLocation']['formatted'])) {
            $petition->setCurrentLocationAddress($PetitionData['details']['currentLocation']['formatted']);
        };
        $petition->setBirthDate(new DateTime(date('Y-m-d H:i:s.u', strtotime($PetitionData['details']['document']['birthDate']))));
        $petition->setbirthPlace($PetitionData['details']['document']['birthPlace']);
        $petition->setPasportIssueOrgan($PetitionData['details']['document']['source']);
        $petition->setPasportSeries($PetitionData['details']['document']['series']);
        $petition->setPasportNumber($PetitionData['details']['document']['number']);
        $petition->setPasportIssueOrgan($PetitionData['details']['document']['source']);
        $petition->setPasportDateObtain(new DateTime(date('Y-m-d H:i:s.u', strtotime($PetitionData['details']['document']['dateObtain']))));
        $createDate = new DateTime(date('Y-m-d H:i:s.u', strtotime($PetitionData['details']['createdTs'])));
        $petition->setCreatedTs($createDate);

        $basicEducationType = $PetitionData['details']['basicEducationType']['name'];
        $studyForms = $PetitionData['details']['studyForm']['name'];
        $financialType = $PetitionData['details']['financialType']['name'];
        $specialisation = explode(' ', ($PetitionData['details']['admissionPlan']['specialityComputedName']));

        $faculty = $this->convertSpeciality($basicEducationType, $studyForms, $financialType, $specialisation[0]);

        if (is_array($faculty)) {

            $petition->setFaculty($faculty['Faculty']);
            $petition->setCanPay($faculty['CanPay']);
            $petition->setHaveErrorInPetition($faculty['HaveErrorInPetition']);
            if (!str_contains($petition->getComment(), $faculty['Comment'])) {
                $petition->setComment($petition->getComment() . ' ' . $faculty['Comment']);
            }
        } else {
            $petition->setFaculty($faculty);
        }
        if ($PetitionData['details']['educationDocumentGPA'] != 0) {
            $petition->seteducationDocumentGPA(number_format($PetitionData['details']['educationDocumentGPA'], 6));
        } else {
            $petition->seteducationDocumentGPA(0);
        }
        $petition->setStatus($this->convertStatus($PetitionData['details']['status']['name']));
        $petition->setCanceled($PetitionData['details']['canceled']);
        $petition->setAdmission($this->getCurrentAdmission());
        $petition->setEducationDocumentName($PetitionData['details']['educationDocument']['type']['name']);
        $petition->setSchoolName($PetitionData['details']['basicEducationOrganizationPlain']);
        $petition->setEducationDocumentNumber($PetitionData['details']['educationDocument']['number']);
        $petition->setNeedStudentAccommondation($PetitionData['details']['needDormitory']);
        $petition->setAttaches($PetitionData['details']['attaches']);
        $petition->setAdmissionPlanPosition($this->convertAdmissionPlanPosition($petition->getFaculty(),$petition->getAdmission()));
        return $petition;
    }

    private
    function getCurrentAdmission()
    {
        return $this->admissionRepository->findOneBy(['status' => $this->admissionStatusRepository->findOneBy(['Name' => 'RUNNING'])]);
    }

    private
    function convertAdmissionPlanPosition($Faculty,$Admission)
    {
        dump($Faculty);
        dump($Admission);
        dump($this->admissionPlanRepository->findOneBy(['faculty'=>$Faculty,'admission'=>$Admission]));
        return $this->admissionPlanRepository->findOneBy(['faculty'=>$Faculty,'admission'=>$Admission]);
    }

    private
    function convertSpeciality($basicEducationType, $studyForms, $financialType, $specialisation)
    {
        $basicEducationType = $this->educationTypeRepository->findOneBy(['Name' => $basicEducationType]);
        $studyForms = $this->educationFormRepository->findOneBy(['Name' => $studyForms]);
        $financialType = $this->financialTypeRepository->findOneBy(['name' => $financialType]);
        $specialisation = $this->specializationRepository->findOneBy(['Code' => $specialisation]);

        $result = $this->facultyRepository->findOneBy([
            'EducationType' => $basicEducationType,
            'EducationForm' => $studyForms,
            'financialType' => $financialType,
            'Specialization' => $specialisation]);
        if (($financialType->getName() == 'CONTRACT') and $result == null) {
            $result['Faculty'] = $this->facultyRepository->findOneBy([
                'EducationType' => $basicEducationType,
                'EducationForm' => $studyForms,
                'financialType' => $this->financialTypeRepository->findOneBy(['name' => 'BUDGET']),
                'Specialization' => $specialisation]);
            $result['CanPay'] = true;
            $result['HaveErrorInPetition'] = true;
            $result['Comment'] = 'Ошибка в заявлении. Указана коммерческая основа,  но в плане приёма такой связки специальность-тип обучения не существует.';
        } else if (($financialType->getName() == 'BUDGET') and $result == null) {

            $result['Faculty'] = $this->facultyRepository->findOneBy([
                'EducationType' => $basicEducationType,
                'EducationForm' => $studyForms,
                'financialType' => $this->financialTypeRepository->findOneBy(['name' => 'CONTRACT']),
                'Specialization' => $specialisation]);
            $result['CanPay'] = true;
            $result['HaveErrorInPetition'] = true;
            $result['Comment'] = 'Ошибка в заявлении. Указана бюджетная основа, но в плане приёма такой связки специальность-тип обучения не существует.';
        }
        return $result;
    }

    private
    function convertPhone($phone): string
    {
        $phone = str_replace('-', '', $phone);
        $phone = str_replace(' ', '', $phone);
        $phone = str_replace('(', '', $phone);
        $phone = str_replace(')', '', $phone);
        $phone = str_replace('+', '', $phone);
        if (substr($phone, 0, 1) == 8) {

            $phone = ltrim($phone, '8');

            $phone = '7' . $phone;
        }
        if (substr($phone, 0, 1) == 7) {
            $phone = '+' . $phone;
        }
        if (substr($phone, 0, 1) == 9) {
            $phone = '+7' . $phone;
        }
        return $phone;
    }

    private
    function convertStatus($status): AbiturientPetitionStatus
    {
        return $this->abiturientPetitionStatusRepository->findOneBy(['name' => $status]);
    }

    private
    function convertGender($gender)
    {
        return $this->personaGenderRpository->findOneBy(['genderName' => $gender]);
    }

    private
    function push($topics, $type, $icon, $header, $message,): Response
    {
        $update = new Update(
            $topics,
            json_encode(['type' => $type, 'header' => $header, 'icon' => $icon, 'message' => $message])
        );
        $this->hub->publish($update);

        return new Response('published!');
    }
}