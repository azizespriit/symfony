<?php
namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\RouterInterface;
use App\Entity\Apprenant;

class MailerService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function reclame(string $email): void
    {
        $emailMessage = (new TemplatedEmail())
            ->from('no-reply@gmail.com')
            ->to($email)
            ->subject('Votre reclamation en cours de traitement')
            ->htmlTemplate('emails/reclamation.html.twig');
           

        $this->mailer->send($emailMessage);
    }


}
