<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeService
{
    private $privateKey;
    private $urlGenerator;

    public function __construct(string $stripePrivateKey, UrlGeneratorInterface $urlGenerator)
    {
        $this->privateKey = $stripePrivateKey;
        $this->urlGenerator = $urlGenerator;
        Stripe::setApiKey($this->privateKey);
    }

    public function createCheckoutSession(int $commandeId, string $email, float $totalAmount, array $items): Session
    {
        $lineItems = [];
        
        foreach ($items as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item['nom'],
                        'description' => $item['description'] ?? '',
                    ],
                    'unit_amount' => (int)($item['prix'] * 100), // Convert to cents
                ],
                'quantity' => $item['quantite'],
            ];
        }

        return Session::create([
            'customer_email' => $email,
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $this->urlGenerator->generate('commande_confirmation', [
                'id' => $commandeId,
                'payment_intent' => '{CHECKOUT_SESSION_ID}'
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->urlGenerator->generate('panier_view', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }
} 