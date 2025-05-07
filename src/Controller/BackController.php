<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BackController extends AbstractController
{
    #[Route('/back', name: 'app_backA')]
    public function accounts(): Response
    {
        return $this->render('baseback.html.twig'); // Create this template if it doesn't exist
    }
}
