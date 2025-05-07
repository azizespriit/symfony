<?php

namespace App\Service;

use Mailjet\Client;
use Mailjet\Resources;

class MailjetService
{
    private $mailjet;

    public function __construct(string $apiKey, string $apiSecret)
    {
        $this->mailjet = new Client($apiKey, $apiSecret, true, ['version' => 'v3.1']);
    }

    public function sendConfirmationEmail(string $toEmail, string $toName, int $codeConfirmation)
    {
        $htmlContent = "
        <h2 style='color: #007BFF;'>🎉 Félicitations {$toName} ! Votre réservation est confirmée 🎉</h2>
        <p>Bonjour <strong>{$toName}</strong>,</p>
        <p>Nous sommes ravis de vous compter parmi les participants de <strong>Sportify</strong> !</p>

        <h3>🔎 Récapitulatif de votre réservation :</h3>
        <ul>
            <li><strong>Email :</strong> {$toEmail}</li>
        </ul>

        <h3 style='color: #28a745;'>🚀 Votre code de confirmation : <strong>{$codeConfirmation}</strong></h3>

        <p style='margin-top: 30px;'>
            Notre équipe avance chaque jour avec une seule ambition :  
            <strong>faire de cet événement une réussite dont vous serez fier !</strong><br><br>
            À très bientôt sur le terrain !
        </p>

        <p style='margin-top: 20px;'>L'équipe de <strong>Sportify</strong> 🏆</p>
        ";

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "wiemtanazefti@gmail.com",  
                        'Name' => "Sportify Team"
                    ],
                    'To' => [
                        [
                            'Email' => $toEmail,
                            'Name' => $toName
                        ]
                    ],
                    'Subject' => "Confirmation de votre réservation",
                    'HTMLPart' => $htmlContent,
                ]
            ]
        ];

        $response = $this->mailjet->post(Resources::$Email, ['body' => $body]);

        if (!$response->success()) {
            throw new \Exception('Erreur lors de l\'envoi de l\'email : ' . $response->getStatus());
        }

        return $response->success();
    }
}
