<?php

namespace App\Repository;

use App\Entity\Commentaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommentaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commentaire::class);
    }

    public function findByPublication($publication)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.publication = :publication')
            ->setParameter('publication', $publication)
            ->orderBy('c.datee', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByUser($user)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.datee', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countByUser($user): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}