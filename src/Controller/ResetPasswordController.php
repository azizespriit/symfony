<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordRequestType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;

class ResetPasswordController extends AbstractController
{
    #[Route('/reset-password-request', name: 'app_reset_password_request')]
    public function request(
        Request $request, 
        UserRepository $userRepository, 
        TokenGeneratorInterface $tokenGenerator, 
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        LoggerInterface $logger
    ): Response
    {
        $form = $this->createForm(ResetPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $email]);

            // Even if the user doesn't exist, don't reveal it for security reasons
            if ($user) {
                try {
                    // Generate a unique token
                    $token = $tokenGenerator->generateToken();
                    
                    // Set the token and expiry (24 hours from now)
                    $user->setReset_token($token);
                    $user->setToken_expiry(new \DateTime('+24 hours'));
                    
                    $entityManager->persist($user);
                    $entityManager->flush();

                    // Send reset email
                    $resetUrl = $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                    $emailMessage = (new Email())
                        ->from('bgbassem8@gmail.com')
                        ->to($user->getEmail())
                        ->subject('Password Reset Request')
                        ->html(
                            $this->renderView('emails/reset_password.html.twig', [
                                'resetUrl' => $resetUrl,
                                'user' => $user
                            ])
                        );

                    $mailer->send($emailMessage);
                    
                    // Add debug message to see if this part is reached
                    $logger->info("Password reset: Email sent to: " . $user->getEmail() . " with token: " . $token);
                } catch (\Exception $e) {
                    // Log the error but don't reveal it to the user
                    $logger->error("Password reset ERROR: " . $e->getMessage());
                }
            } else {
                // Log if user not found (only for debugging)
                $logger->warning("Password reset: User not found for email: " . $email);
            }

            $this->addFlash('success', 'If your email exists in our system, you will receive a password reset link shortly.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function reset(
        string $token, 
        Request $request, 
        UserRepository $userRepository, 
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        // Find user by token
        $user = $userRepository->findOneBy(['reset_token' => $token]);

        // Check if token exists and is not expired
        if (!$user || $user->getToken_expiry() < new \DateTime()) {
            $this->addFlash('error', 'This password reset link has expired or is invalid.');
            return $this->redirectToRoute('app_reset_password_request');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the new password
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            // Update the user
            $user->setPassword($hashedPassword);
            $user->setReset_token(''); // Clear the token
            $user->setToken_expiry(new \DateTime()); // Expire the token

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Your password has been successfully reset. You can now log in with your new password.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }
} 