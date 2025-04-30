<?php

namespace App\Repository;

use App\Entity\PanierProduit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PanierProduit>
 *
 * @method PanierProduit|null find($id, $lockMode = null, $lockVersion = null)
 * @method PanierProduit|null findOneBy(array $criteria, array $orderBy = null)
 * @method PanierProduit[]    findAll()
 * @method PanierProduit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PanierProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PanierProduit::class);
    }

    public function save(PanierProduit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PanierProduit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
} 