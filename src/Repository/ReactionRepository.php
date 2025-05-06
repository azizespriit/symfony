<?php 

namespace App\Repository;

use App\Entity\Publication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Reaction;

class ReactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reaction::class);
    }

    // Add these methods to your ReactionRepository
    public function findOneByPublicationAndUser(int $publicationId, int $userId): ?Reaction
    {
        return $this->createQueryBuilder('r')
            ->where('r.publication = :publicationId')
            ->andWhere('r.user = :userId')
            ->setParameter('publicationId', $publicationId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countReactionsGroupedByType(int $publicationId)
    {
        return $this->createQueryBuilder('r')
            ->select('r.type as type, COUNT(r.id) as count')
            ->where('r.publication = :publicationId')
            ->setParameter('publicationId', $publicationId)
            ->groupBy('r.type')
            ->getQuery()
            ->getResult();
    }

    public function findUserReactionOnPublication(int $userId, int $publicationId): ?Reaction
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :userId')
            ->andWhere('r.publication = :publicationId')
            ->setParameter('userId', $userId)
            ->setParameter('publicationId', $publicationId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countByPublicationAndType(int $publicationId, string $type): int
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.publication = :publicationId')
            ->andWhere('r.type = :type')
            ->setParameter('publicationId', $publicationId)
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult();
    }
    
    // Keep your existing methods here...
    public function findAllWithUser()
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->addSelect('u')
            ->orderBy('p.datePub', 'DESC')
            ->getQuery()
            ->getResult();
    }

public function countReactionsByType(int $publicationId): array
{
    return $this->createQueryBuilder('p')
        ->select('r.type, COUNT(r.id) as count')
        ->leftJoin('p.reactions', 'r')
        ->where('p.id = :id')
        ->setParameter('id', $publicationId)
        ->groupBy('r.type')
        ->getQuery()
        ->getResult();
}

public function findBySearch(string $term): array
{
    return $this->createQueryBuilder('p')
        ->where('p.title LIKE :term OR p.content LIKE :term')
        ->setParameter('term', '%' . $term . '%')
        ->orderBy('p.createdAt', 'DESC') // Optionnel : trie par date de création
        ->getQuery()
        ->getResult();
}

}
