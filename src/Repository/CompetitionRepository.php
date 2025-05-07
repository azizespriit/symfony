<?php

namespace App\Repository;

use App\Entity\Competition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Competition>
 *
 * @method Competition|null find($id, $lockMode = null, $lockVersion = null)
 * @method Competition|null findOneBy(array $criteria, array $orderBy = null)
 * @method Competition[]    findAll()
 * @method Competition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompetitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Competition::class);
    }

    // Exemple : méthode personnalisée pour récupérer les compétitions futures
    public function findFutureCompetitions(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.dateC > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('c.dateC', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Tu peux ajouter ici d'autres méthodes personnalisées si besoin
}
