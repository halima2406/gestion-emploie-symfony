<?php

namespace App\Repository;
use App\DTO\EmployeSearchFormDto;




use App\Entity\Employe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Employe>
 */
class EmployeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employe::class);
    }

   
    public function save(Employe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
    
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // src/Repository/EmployeRepository.php

    // src/Repository/EmployeRepository.php


    public function searchPaginated(EmployeSearchFormDto $search, int $page, int $limit): array
    {
        // --- liste paginée ---
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.departement', 'd')->addSelect('d');

        // filtres
        if ($search->numero) {
            $qb->andWhere('e.numero LIKE :num')->setParameter('num', '%'.$search->numero.'%');
        }
        if ($search->departement) {
            $qb->andWhere('e.departement = :dep')->setParameter('dep', $search->departement);
        }
        if ($search->statut === 'actif') {
            $qb->andWhere('e.isDeleted = :del')->setParameter('del', false);
        } elseif ($search->statut === 'archive') {
            $qb->andWhere('e.isDeleted = :del')->setParameter('del', true);
        }

        // tri: le plus récent d'abord
        $qb->orderBy('e.createAt', 'DESC')
        ->addOrderBy('e.numero', 'DESC');

        // pagination
        $qb->setFirstResult(($page - 1) * $limit)
        ->setMaxResults($limit);

        $employes = $qb->getQuery()->getResult();

        // --- total (compte) : on repart d'un QB propre, mêmes filtres, mais sans order/limit ---
        $countQb = $this->createQueryBuilder('e');

        if ($search->numero) {
            $countQb->andWhere('e.numero LIKE :num')->setParameter('num', '%'.$search->numero.'%');
        }
        if ($search->departement) {
            $countQb->andWhere('e.departement = :dep')->setParameter('dep', $search->departement);
        }
        if ($search->statut === 'actif') {
            $countQb->andWhere('e.isDeleted = :del')->setParameter('del', false);
        } elseif ($search->statut === 'archive') {
            $countQb->andWhere('e.isDeleted = :del')->setParameter('del', true);
        }

        $count = (int) $countQb
            ->select('COUNT(e.numero)')   // <-- pas e.id !
            ->getQuery()
            ->getSingleScalarResult();

        return [$employes, $count];
    }
}
