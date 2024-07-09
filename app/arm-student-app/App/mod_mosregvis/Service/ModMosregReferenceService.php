<?php

namespace App\mod_mosregvis\Service;

use App\MainApp\Entity\College;
use App\mod_mosregvis\Entity\mosregApiConnection;
use App\mod_mosregvis\Entity\MosregVISCollege;
use App\mod_mosregvis\Entity\reference_eduYearStatus;
use App\mod_mosregvis\Entity\reference_SpoEducationYear;
use App\mod_mosregvis\Entity\reference_ufttDocumentType;
use App\mod_mosregvis\Repository\reference_SpoEducationYearRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\NotSupported;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ModMosregReferenceService extends ModMosregApiProvider
{
    private EntityManagerInterface $entityManager;

    /**
     * @param mosregApiConnection $apiConnection
     * @param HttpClientInterface $client
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        mosregApiConnection    $apiConnection,
        HttpClientInterface    $client,
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
        parent::__construct($apiConnection, $client);
    }

    public function updateReference(string $name = ''): void
    {
        if ($name != '' && $name != 'full') {
        } elseif ($name == '' or $name == 'full') {
            foreach ($this->getReference() as $key => $value) {
                $func = 'update' . $key;
                $this->$func($value);
            }
        }
    }

    #[NoReturn] private function updateSpoEducationYear(array $SpoEducationYear): void
    {

        /**
         * @var reference_SpoEducationYearRepository $repository ;
         */
        $repository = $this->entityManager->getRepository(reference_SpoEducationYear::class);
        $yearStatusRepository = $this->entityManager->getRepository(reference_eduYearStatus::class);
        $collegeRepository = $this->entityManager->getRepository(MosregVISCollege::class);
        foreach ($SpoEducationYear as $item) {
            if (count($findItem = $repository->findBy(['guid' => $item['id']])) == 0) {
                $reference = new reference_SpoEducationYear();
            } else {
                $reference = $findItem;
            }
            /**
             * @var reference_SpoEducationYear $reference
             */
            $reference->setGuid($item['id']);
            $reference->setName($item['educationYearDictionary']['name']);
            if ($currentYearStatus = $yearStatusRepository->findBy(['code' => $item['educationYearStatus']['code']])) {
                $reference->setYearStatus($currentYearStatus[0]);
            }
            $reference->setCollege($collegeRepository->findBy(['visCollegeId' => $this->apiConnection->getCollegeId()])[0]);

            $reference->setStartDate(date_create($item['educationYearDictionary']['startDate']));
            $reference->setEndDate(date_create($item['educationYearDictionary']['endDate']));
            $this->entityManager->persist($reference);
            $this->entityManager->flush();
        }

        dd('end');

    }

    private function updateeduYearStatus(array $eduYearStatus): void
    {
        $repository = $this->entityManager->getRepository(reference_eduYearStatus::class);
        foreach ($eduYearStatus as $item) {
            if (count($repository->findBy(['code' => $item['code']])) == 0) {
                $reference = new reference_eduYearStatus();

            } else {
                try {
                    $reference = $repository->findBy(['code' => $item['code']])[0];
                } catch (NotSupported $e) {
                }
            }
            $reference->setName($item['name']);
            $reference->setCode($item['code']);
            $reference->setTitle($item['title']);
            $this->entityManager->persist($reference);
            $this->entityManager->flush();
        }
    }

    /**
     * @throws NotSupported
     */
    private function updateReferenceDocumentType(array $ReferenceDocumentType): void
    {
        $documentTypeRepository = $this->entityManager->getRepository(reference_ufttDocumentType::class);
        foreach ($ReferenceDocumentType as $item) {
            if (count($documentTypeRepository->findBy(['code' => $item['code']])) == 0) {
                $documentType = new reference_ufttDocumentType();

            } else {
                try {
                    $documentType = $documentTypeRepository->findBy(['code' => $item['code']])[0];
                } catch (NotSupported $e) {
                }
            }
            $documentType->setName($item['name']);
            $documentType->setCode($item['code']);
            $documentType->setTitle($item['title']);
            $this->entityManager->persist($documentType);
            $this->entityManager->flush();
        }
    }

    /**
     * @param string $name *Опционально. Имя сущности для обновления
     * @return ?array
     * @throws \JsonException
     */
    public
    function getReference(string $name = ''): ?array
    {
        if ($name == 'ReferenceDocumentType') return $this->getReferenceDocumentType();
        elseif ($name == 'getSpoEducationYear') return $this->getSpoEducationYear();
        elseif ($name == 'eduYearStatus') return $this->geteduYearStatus();
        elseif ($name = '' or $name = 'full') {
            dump(' getReference full else');
            $result['ReferenceDocumentType'] = $this->getReferenceDocumentType();
            $result['eduYearStatus'] = $this->geteduYearStatus();
            $result['SpoEducationYear'] = $this->getSpoEducationYear();
        } else {
            $result = array();
        }
        dump($result);
        return $result;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    private
    function getReferenceDocumentType(): array
    {
        if ($this->isApiAvailable()) {
            $response = $this->client->request('GET', $this->apiConnection->getApiUrl() . '/reference/ufttDocumentType',
                [
                    'headers' => array_merge($this->apiConnection->getApiHeaders(), ['Authorization' => 'Token ' . $this->apiConnection->getToken()])
                ]);
        }
        if ($response->getStatusCode() != 200) {
            new Exception(sprintf("Ошибка получения данных организации из API. Код ошибки:%s", $response->getStatusCode()));
        } elseif ($response->getStatusCode() == 401) {
            new Exception(sprintf("Ошибка авторизации API. Код ошибки: 401 Unauthorized"));
        }
        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    private
    function geteduYearStatus(): array
    {
        if ($this->isApiAvailable()) {
            $response = $this->client->request('GET', $this->apiConnection->getApiUrl() . '/spo/reference/eduYearStatus',
                [
                    'headers' => array_merge($this->apiConnection->getApiHeaders(), ['Authorization' => 'Token ' . $this->apiConnection->getToken()])
                ]);
        }
        if ($response->getStatusCode() != 200) {
            throw new Exception(sprintf("Ошибка получения данных организации из API. Код ошибки:%s", $response->getStatusCode()));
        } elseif ($response->getStatusCode() == 401) {
            throw new Exception("Ошибка авторизации API. Код ошибки: 401 Unauthorized ");
        }
        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    private
    function getSpoEducationYear(): array
    {
        if ($this->isApiAvailable()) {
            $response = $this->client->request('GET', $this->apiConnection->getApiUrl() . '/spoEducationYear/search/byOrg?page=0&size=5000&organization=' . $this->apiConnection->getCollegeId() . '&projection=grid',
                [
                    'headers' => array_merge($this->apiConnection->getApiHeaders(), ['Authorization' => 'Token ' . $this->apiConnection->getToken()])
                ]);
        }
        if ($response->getStatusCode() != 200) {
            new Exception(sprintf("Ошибка получения данных организации из API. Код ошибки:%s", $response->getStatusCode()));
        } elseif ($response->getStatusCode() == 401) {
            new Exception(sprintf("Ошибка авторизации API. Код ошибки: 401 Unauthorized "));
        }
        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR)['_embedded']['spoEducationYears'];
    }
}