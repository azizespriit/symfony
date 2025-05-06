<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CustomUserProvider implements UserProviderInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Updated to use loadUserByIdentifier
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // Try to find the user by their email (identifier)
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $identifier]);

        if (!$user) {
            throw new UserNotFoundException("User with email $identifier not found.");
        }

        // Check if the account is locked
        if ($user->getIsLocked()) {
            throw new AuthenticationException('Your account is locked.');
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        // Refresh the user by re-fetching from the database
        if (!$user instanceof User) {
            throw new \LogicException('The user must be an instance of the User class.');
        }

        return $this->entityManager->getRepository(User::class)->find($user->getId_user());
    }

    public function supportsClass(string $class): bool
    {
        // Make sure this provider supports your User entity
        return User::class === $class;
    }
}
