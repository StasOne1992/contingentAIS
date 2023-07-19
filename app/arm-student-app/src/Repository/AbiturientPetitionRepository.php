<?php

namespace App\Repository;

use App\Entity\AbiturientPetition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AbiturientPetition>
 *
 * @method AbiturientPetition|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbiturientPetition|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbiturientPetition[]    findAll()
 * @method AbiturientPetition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AbiturientPetitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbiturientPetition::class);
    }

    public function save(AbiturientPetition $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AbiturientPetition $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return array[] Returns an array of AbiturientPetition GUID's
     */
    public function getAllGUID(): array
    {
        $repoData = $this->findAll();
        $result = array();
        foreach ($repoData as $data) {
            $result[] = $data->getGUID();
        }
        return $result;
    }

    /**
     * @return array[] Returns an array of AbiturientPetition for load to WebSite
     */
    public function getPetitionToSite(): array
    {
        $result = array();

        return $result;
    }

    /**
     * @return int Returns integer - count unique people in admission
     */
    public function getUniquePetitionCount($admission): int
    {
        $connection = $this->getEntityManager()->getConnection();
        $query = "SELECT DISTINCT ON (document_snils)  document_snils
	                    FROM public.abiturient_petition 
	                    WHERE admission_id=$admission AND document_snils!=''";
        $resultSet = $connection->executeQuery($query);


        return count($resultSet->fetchAllAssociative());
    }

//    /**
//     * @return AbiturientPetition[] Returns an array of AbiturientPetition objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AbiturientPetition
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
