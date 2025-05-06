<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function findOneByCin(string $cin): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.cin = :cin')
            ->setParameter('cin', $cin)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function lockUser(User $user): void
    {
        $user->setIsLocked(true);
        $user->setLockoutTime(new \DateTime());
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function unlockUser(User $user): void
    {
        $user->setIsLocked(false);
        $user->setFailedAttempts(0);
        $user->setLockoutTime(null);
        $this->_em->persist($user);
        $this->_em->flush();
    }
}