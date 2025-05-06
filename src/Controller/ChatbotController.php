<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ChatbotService;

class ChatbotController extends AbstractController
{
    #[Route('/chatbot', name: 'app_chatbot')]
    public function chatbot(Request $request, ChatbotService $chatbotService): Response
    {
        $message = $request->query->get('message', ''); // Ou request->request pour POST
        
        $response = $chatbotService->getResponse($message);

        return $this->json([
            'response' => $response
        ]);
    }
}
