<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[Route('/userr')]
final class ProfileController extends AbstractController
{
    #[Route('/{id_user}', name: 'app_user_profile', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id_user}/editprof', name: 'app_user_editprof', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if password was changed
            $password = $form->get('password')->getData();
            if ($password) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $password
                );
                $user->setPassword($hashedPassword);
            }

            // Update the timestamp
            $user->setUpdateAt(new \DateTimeImmutable());

            $entityManager->flush();

            $this->addFlash('success', 'User updated successfully!');
            return $this->redirectToRoute('app_user_profile', ['id_user' => $user->getId_user()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('editprof.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id_user}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$user->getId_user(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'User deleted successfully!');
        } else {
            $this->addFlash('error', 'Invalid CSRF token!');
        }

        return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
    }
}