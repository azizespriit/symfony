<?php

namespace App\Repository;

use App\Entity\Jour;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Jour>
 */
class JourRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Jour::class);
    }

    /**
     * @return Jour[] Returns an array of Jour objects for a specific objectif
     */
    public function findByObjectifId(int $objectifId): array
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.objectif = :objectifId')
            ->setParameter('objectifId', $objectifId)
            ->orderBy('j.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
