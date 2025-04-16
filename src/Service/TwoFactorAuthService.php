<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\User\UserInterface;

class TwoFactorAuthService {
private MailerInterface $mailer;

public function __construct(MailerInterface $mailer) {
$this->mailer = $mailer;
}

public function generateAndSendCode(UserInterface $user): string {
$code = random_int(100000, 999999); // 6-digit code
$this->sendEmail($user->getEmail(), $code);
return $code;
}

private function sendEmail(string $toEmail, string $code): void {
$email = (new Email())
->from('anouarsalhi123@gmail.com') // 👈 Sender
->to($toEmail)
->subject('🔐 Your 2FA Code')
->text("Your verification code is: $code (valid for 5 minutes)");

$this->mailer->send($email);
}
}