<?php

namespace App\Repository;

use App\Entity\Departements;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\DTO\EmployeSearchFormDto;

/**
 * @extends ServiceEntityRepository<Departements>
 */
class DepartementsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Departements::class);
    }

   


    public function save(Departements $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
    
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countEmployeesFor(array $ids): array
    {
        if (!$ids) return [];

        $rows = $this->createQueryBuilder('d')
            ->select('d.id AS depId, COUNT(e.numero) AS nb')
            ->leftJoin('App\Entity\Employe', 'e', 'WITH', 'e.departement = d AND e.isDeleted = false')
            ->where('d.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->groupBy('d.id')
            ->getQuery()
            ->getArrayResult();

     
        return array_column($rows, 'nb', 'depId');
    }


  

}
