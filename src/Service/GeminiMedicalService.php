<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class GeminiMedicalService
{
    private HttpClientInterface $httpClient;
    private string $apiKey = "AIzaSyCVQHI_ArRIWqOmqDwi0D1cC5kBKXq-gVI"; // 🔴 Replace with your actual API key
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     * Translates text from French to English.
     *
     * @param string $text The text in French to translate
     * @return string|null The translated text in English or an error message
     */
    public function translateFrenchToEnglish(string $text): ?string
    {
        $prompt = "You are an expert translator. Your task is to translate the provided French text into accurate, natural, and idiomatic English.

        ### Instructions:
        - Translate the text **exactly** as provided, preserving the meaning and tone.
        - Use **natural and fluent English** suitable for general communication.
        - Do not add or omit any information.
        - If the input text is ambiguous, provide the most likely translation and log a warning.
        - Return the translation as plain text.

        ### French Text:
        \"$text\"

        Provide the English translation below.";

        return $this->callGeminiApi($prompt);
    }

    /**
     * Generates a concise description based on the content of a publication.
     *
     * @param string $content The content of the publication
     * @return string|null The generated description or an error message
     */
    public function generatePublicationDescription(string $content): ?string
    {
        $prompt = "You are an expert content creator tasked with generating a concise, engaging, and professional description for a publication based on its content. Your goal is to craft a description that captivates readers and reflects the essence of the content, even if the input is brief or unclear.
    
        ### Instructions:
        - **Length**: Create a description of **50-100 words**.
        - **Tone and Style**: Use **clear, professional, and engaging language** suitable for a general audience. Make it compelling and inviting.
        - **Content Handling**:
          - If the content is detailed, highlight its **main ideas**, **key points**, or **unique aspects**.
          - If the content is vague, short, or fragmented (e.g., a few words or a phrase), **infer a plausible context** based on common themes (e.g., professional, educational, or creative topics) and craft a description that adds value while staying relevant.
          - Do not invent false or unverified details, but feel free to reformulate or expand on the input creatively.
        - **Output**: Return the description as **plain text**, avoiding any disclaimers or notes about the input's vagueness.
        - Ensure the description is **self-contained** and does not reference the prompt or instructions.
    
        ### Publication Content:
        \"$content\"
    
        Provide the description below.";
    
        return $this->callGeminiApi($prompt);
    }

    /**
     * Helper method to call the Gemini API with retry logic.
     *
     * @param string $prompt The prompt to send to the API
     * @return string|null The API response or an error message
     */
    private function callGeminiApi(string $prompt): ?string
    {
        $url = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=" . $this->apiKey;
        $maxRetries = 3;
        $retryDelay = 2;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = $this->httpClient->request('POST', $url, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'contents' => [
                            ['parts' => [['text' => $prompt]]]
                        ]
                    ],
                ]);

                $statusCode = $response->getStatusCode();
                $data = $response->toArray();

                $this->logger->info("Gemini API Response (Attempt: {$attempt})", [
                    'status' => $statusCode,
                    'response' => $data
                ]);

                if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $this->logger->warning("Gemini API returned an unexpected response", ['response' => $data]);

                    if ($attempt < $maxRetries) {
                        sleep($retryDelay);
                        continue;
                    }

                    return "Unable to process the request. Please try again later.";
                }

                return trim($data['candidates'][0]['content']['parts'][0]['text']);

            } catch (\Exception $e) {
                $this->logger->error("Gemini API Exception (Attempt: {$attempt})", [
                    'exception' => $e->getMessage(),
                    'stacktrace' => $e->getTraceAsString(),
                ]);

                if ($attempt < $maxRetries) {
                    sleep($retryDelay);
                    continue;
                }

                return "A technical error occurred. Please try again later.";
            }
        }

        return "Unable to process the request at this time.";
    }


}