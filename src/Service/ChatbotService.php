<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

class ChatbotService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;
    private string $apiEndpoint;
    private LoggerInterface $logger;
    
    public function __construct(
        HttpClientInterface $httpClient, 
        ParameterBagInterface $params,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->apiKey = $params->get('app.chatbot.api_key');
        $this->apiEndpoint = $params->get('app.chatbot.api_endpoint');
        $this->logger = $logger;
    }

    /**
     * Send a message to the chatbot and get a response
     *
     * @param string $message The user's message
     * @param array $conversationHistory Optional conversation history for context
     * @return array The response data
     */
    public function sendMessage(string $message, array $conversationHistory = []): array
    {
        try {
            // Check if we have an API key
            if (empty($this->apiKey)) {
                $this->logger->error('ChatbotService: API key is missing or not configured correctly');
                return [
                    'success' => false,
                    'error' => 'API key is not configured. Please update the CHATBOT_API_KEY in your .env.local file.'
                ];
            }
            
            // Log the attempt
            $this->logger->info('ChatbotService: Sending message to Gemini API', [
                'endpoint' => $this->apiEndpoint,
                'message' => $message
            ]);
            
            // Build the full URL with API key for Gemini
            $fullUrl = $this->apiEndpoint . '?key=' . $this->apiKey;
            
            // Format the request data for Gemini API
            $requestData = $this->formatGeminiRequest($message, $conversationHistory);
            
            // Send the request to Gemini API
            $response = $this->httpClient->request('POST', $fullUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $requestData,
            ]);

            $statusCode = $response->getStatusCode();
            $this->logger->info('ChatbotService: API response status code: ' . $statusCode);
            
            $data = $response->toArray();
            $this->logger->info('ChatbotService: Received response from Gemini API');
            
            $extractedMessage = $this->extractGeminiResponse($data);
            return [
                'success' => true,
                'message' => $extractedMessage,
                'raw_response' => $data,
            ];
        } catch (\Exception $e) {
            $this->logger->error('ChatbotService: Error when calling Gemini API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format request data for the Gemini API
     */
    private function formatGeminiRequest(string $currentMessage, array $history): array
    {
        $formattedContents = [];
        
        // Add role description as a system message
        $formattedContents[] = [
            'role' => 'user',
            'parts' => [
                [
                    'text' => 'You are a helpful assistant for a sports and fitness app called Sportify. Provide concise, helpful responses about fitness, nutrition, workout routines, and general health questions. Keep responses friendly and motivational.'
                ]
            ]
        ];
        
        $formattedContents[] = [
            'role' => 'model',
            'parts' => [
                [
                    'text' => 'I understand. I am now a helpful assistant for the Sportify fitness app. I will provide friendly, concise, and motivational responses about fitness, nutrition, workout routines, and health questions. How can I help you today?'
                ]
            ]
        ];
        
        // Add conversation history
        $userTurn = true;
        foreach ($history as $exchange) {
            if (isset($exchange['user']) && $userTurn) {
                $formattedContents[] = [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => $exchange['user']
                        ]
                    ]
                ];
                $userTurn = false;
            }
            
            if (isset($exchange['assistant']) && !$userTurn) {
                $formattedContents[] = [
                    'role' => 'model',
                    'parts' => [
                        [
                            'text' => $exchange['assistant']
                        ]
                    ]
                ];
                $userTurn = true;
            }
        }
        
        // Add the current message
        $formattedContents[] = [
            'role' => 'user',
            'parts' => [
                [
                    'text' => $currentMessage
                ]
            ]
        ];
        
        return [
            'contents' => $formattedContents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 800,
                'topP' => 0.95,
                'topK' => 40
            ]
        ];
    }

    /**
     * Extract the response text from the Gemini API response
     */
    private function extractGeminiResponse(array $responseData): string
    {
        // Try to extract the text from Gemini API format
        if (!empty($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            return $responseData['candidates'][0]['content']['parts'][0]['text'];
        }
        
        // Fallback for older API versions
        if (!empty($responseData['candidates'][0]['text'])) {
            return $responseData['candidates'][0]['text'];
        }
        
        // Log the structure if we can't find the expected format
        $this->logger->warning('ChatbotService: Unable to extract message from Gemini response', [
            'responseData' => $responseData
        ]);
        
        return 'Sorry, I could not generate a response. Please try again later.';
    }
} 