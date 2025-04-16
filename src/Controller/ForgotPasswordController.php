<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Repository\UserRepository;

class ForgotPasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgot(Request $request, MailerInterface $mailer, UserRepository $userRepo): Response
    {
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'label' => 'Your email address',
                'attr' => ['placeholder' => 'Enter your email'],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Send Reset Link'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $userRepo->findOneBy(['email' => $data['email']]);

            if ($user) {
                // Here you should generate a token and save it — we can add that later

                $email = (new Email())
                    ->from('anouarsalhi123@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Password Reset Request')
                    ->html('<p>To reset your password, click <a href="#">here</a>.</p>');

                $mailer->send($email);
                $this->addFlash('success', 'If your email is registered, a reset link has been sent.');
            } else {
                $this->addFlash('warning', 'If your email is registered, a reset link has been sent.');
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forgot_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
