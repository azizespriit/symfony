<?php

namespace App\Repository;

use App\Entity\Publication;
use App\Entity\Commentaire;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PublicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Publication::class);
    }

    public function findAllWithUser()
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->addSelect('u')
            ->orderBy('p.datePub', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByUser($user)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.datePub', 'DESC')
            ->getQuery()
            ->getResult();
    }
    public function findByContent(?string $searchTerm): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->addSelect('u');

        if ($searchTerm) {
            $qb->where('p.contenu LIKE :searchTerm')
               ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        return $qb->getQuery()
                  ->getResult();
    }
   
    public function findByP(int $id)
    {
        return $this->createQueryBuilder('p')
        ->leftJoin('p.user', 'u')
        ->addSelect('u')
        ->leftJoin('p.commentaires', 'c')
        ->addSelect('c')
        ->leftJoin('p.reactions', 'r')
        ->addSelect('r')
        ->leftJoin('c.user', 'cu')
        ->addSelect('cu')
        ->where('p.id = :id')
        ->setParameter('id', $id)
        ->getQuery()
        ->getOneOrNullResult();
    }

    public function findRecent(int $maxResults): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.datePub', 'DESC')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult();
    }

    public function findMostCommented(int $maxResults): array
    {
        return $this->createQueryBuilder('p')
            ->select('p, COUNT(c.id) as commentCount')
            ->leftJoin('p.commentaires', 'c')
            ->groupBy('p.id')  // Changed from p.id to p.Id_pub
            ->orderBy('commentCount', 'DESC')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult();
    }

    public function findByDate(\DateTimeInterface $date)
    {
        $start = new \DateTime($date->format('Y-m-d').' 00:00:00');
        $end = new \DateTime($date->format('Y-m-d').' 23:59:59');

        return $this->createQueryBuilder('p')
            ->andWhere('p.datePub BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('p.datePub', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function searchByContent(string $keyword)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.contenu LIKE :keyword')
            ->setParameter('keyword', '%'.$keyword.'%')
            ->orderBy('p.datePub', 'DESC')
            ->getQuery()
            ->getResult();
    }
   
    public function countReactionsGroupedByType($publicationId): array
    {
        return $this->createQueryBuilder('r')
            ->select('r.type, COUNT(r.id) as count')
            ->where('r.publication = :pubId')
            ->setParameter('pubId', $publicationId)
            ->groupBy('r.type')
            ->getQuery()
            ->getResult();
    }

    // src/Repository/PublicationRepository.php
    public function findAllWithUserAndComments()
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->leftJoin('p.commentaires', 'c')
            ->addSelect('u')
            ->addSelect('c')
            ->orderBy('p.datePub', 'DESC')
            ->getQuery()
            ->getResult();
    }
   

    public function findAllOrderedByDate(string $order = 'DESC'): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.datePub', $order)
            ->getQuery()
            ->getResult();
    }
    
    public function findByContenu(string $term, string $order = 'DESC'): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.contenu LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('p.datePub', $order)
            ->getQuery()
            ->getResult();
    }
    
    
}
    