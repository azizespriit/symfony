<?php

namespace App\Controller;

use App\Service\ChatbotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/front')]
#[IsGranted('ROLE_USER')]
class ChatbotController extends AbstractController
{
    #[Route('/chatbot', name: 'app_front_chatbot')]
    public function index(): Response
    {
        return $this->render('chatbot/index.html.twig', [
            'controller_name' => 'ChatbotController',
        ]);
    }

    #[Route('/chatbot/message', name: 'app_chatbot_message', methods: ['POST'])]
    public function message(Request $request, ChatbotService $chatbotService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = $data['message'] ?? '';
        $history = $data['history'] ?? [];

        if (empty($message)) {
            return $this->json([
                'success' => false,
                'error' => 'No message provided',
            ]);
        }

        $response = $chatbotService->sendMessage($message, $history);

        return $this->json($response);
    }
} 