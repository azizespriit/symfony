<?php

namespace App\Repository;

use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reclamation>
 */
class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }
    public function searchByNameOrEmail(string $searchTerm): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.name LIKE :searchTerm OR r.email LIKE :searchTerm')
            ->setParameter('searchTerm', '%'.$searchTerm.'%')
            ->orderBy('r.updated', 'DESC')
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return Reclamation[] Returns an array of Reclamation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reclamation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function getReclamationsByUser(): array
{
    return $this->createQueryBuilder('r')
        ->select('IDENTITY(r.user_id) AS userId', 'u.email', 'COUNT(r.id) AS reclamationCount')
        ->join('r.user_id', 'u')
        ->groupBy('u.id_user')
        ->orderBy('reclamationCount', 'DESC')
        ->getQuery()
        ->getResult();
}

}
