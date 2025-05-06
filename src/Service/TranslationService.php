<?php
// src/Service/TranslationService.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class TranslationService
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private string $apiUrl;
    private int $timeout;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        string $apiUrl,
        int $timeout
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->apiUrl = $apiUrl;
        $this->timeout = $timeout;
    }

    public function translate(string $text, string $targetLanguage): string
    {
        try {
            $response = $this->httpClient->request('POST', $this->apiUrl, [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'q' => $text,
                    'source' => 'auto',
                    'target' => $targetLanguage,
                    'format' => 'text'
                ],
                'timeout' => $this->timeout
            ]);

            $result = $response->toArray();
            return $result['translatedText'] ?? $text;

        } catch (\Exception $e) {
            $this->logger->error('Translation failed', [
                'error' => $e->getMessage(),
                'text' => $text,
                'target' => $targetLanguage
            ]);
            
            throw new \RuntimeException('Translation service unavailable', 0, $e);
        }
    }
}