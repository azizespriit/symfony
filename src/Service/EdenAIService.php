<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EdenAIService
{
    private $httpClient;
    private $params;
    private $apiKey;

    public function __construct(HttpClientInterface $httpClient, ParameterBagInterface $params)
    {
        $this->httpClient = $httpClient;
        $this->params = $params;
        // Récupération de la clé API depuis les paramètres
        $this->apiKey = $this->params->get('eden_ai_api_key');
    }

    /**
     * Génère une image à partir d'une description
     * 
     * @param string $description La description textuelle pour générer l'image
     * @return array Informations sur l'image générée (url, etc.)
     */
    public function generateImageFromText(string $description): array
    {
        try {
            // Vérification que la clé API est bien définie
            if (empty($this->apiKey)) {
                return [
                    'success' => false,
                    'error' => 'Clé API Eden AI non configurée'
                ];
            }
            
            // Configuration correcte des paramètres pour l'API Eden AI
            // Utilisation d'un fournisseur valide pour la génération d'images
            $requestBody = [
                'providers' => 'openai', // Utiliser OpenAI comme fournisseur
                'text' => $description,
                'resolution' => '1024x1024', // Résolution supportée par OpenAI
                'num_images' => 1
            ];
            
            // Log pour debug
            error_log("Sending request to Eden AI with API key: " . substr($this->apiKey, 0, 10) . "...");
            error_log("Request body: " . json_encode($requestBody));
            
            $response = $this->httpClient->request('POST', 'https://api.edenai.run/v2/image/generation', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $requestBody,
                'timeout' => 30 // Augmenter le timeout pour les requêtes de génération d'images
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);
            error_log("Eden AI response status code: " . $statusCode);
            error_log("Eden AI response content: " . substr($content, 0, 200) . "...");
            
            if ($statusCode !== 200) {
                return [
                    'success' => false,
                    'error' => 'Erreur API: ' . $statusCode,
                    'details' => $content
                ];
            }

            // Récupérer la réponse complète
            $data = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'error' => 'Erreur de décodage JSON: ' . json_last_error_msg(),
                    'raw_content' => substr($content, 0, 500)
                ];
            }
            
            // Rechercher l'URL de l'image dans la réponse
            $imageUrl = null;
            
            // Log the full data structure for debugging
            error_log("Response data structure: " . json_encode($data));
            
            // Check if there's an error in the OpenAI response
            if (isset($data['openai']['error'])) {
                $errorMessage = $data['openai']['error']['message'] ?? 'Unknown OpenAI error';
                return [
                    'success' => false,
                    'error' => 'Erreur OpenAI: ' . $errorMessage,
                    'data' => $data
                ];
            }
            
            // Check format we found in testing
            if (isset($data['openai']['status']) && $data['openai']['status'] === 'success') {
                if (isset($data['openai']['items']) && is_array($data['openai']['items']) && !empty($data['openai']['items'])) {
                    if (isset($data['openai']['items'][0]['image_resource_url'])) {
                        $imageUrl = $data['openai']['items'][0]['image_resource_url'];
                        error_log("Found image URL in standard format: " . substr($imageUrl, 0, 50) . "...");
                    }
                }
            }
            
            // Handle direct URL format found in testing
            if (!$imageUrl && isset($data['openai']['results']) && is_array($data['openai']['results']) && !empty($data['openai']['results'])) {
                if (isset($data['openai']['results'][0]['url'])) {
                    $imageUrl = $data['openai']['results'][0]['url'];
                    error_log("Found image URL in results format: " . substr($imageUrl, 0, 50) . "...");
                } elseif (isset($data['openai']['results'][0]['image_url'])) {
                    $imageUrl = $data['openai']['results'][0]['image_url'];
                    error_log("Found image URL in alternate results format");
                } elseif (isset($data['openai']['results'][0]['b64_json'])) {
                    $base64 = $data['openai']['results'][0]['b64_json'];
                    $imageUrl = 'data:image/png;base64,' . $base64;
                    error_log("Found base64 image data");
                }
            }
            
            // First check standard formats from the old code
            if (!$imageUrl && isset($data['openai']) && isset($data['openai']['items']) && is_array($data['openai']['items']) && !empty($data['openai']['items'])) {
                if (isset($data['openai']['items'][0]['image_resource_url'])) {
                    $imageUrl = $data['openai']['items'][0]['image_resource_url'];
                    error_log("Found image URL in standard format");
                } elseif (isset($data['openai']['items'][0]['image'])) {
                    $imageUrl = $data['openai']['items'][0]['image'];
                    error_log("Found image in items[0]['image'] format");
                }
            }
            
            // Si aucune structure standard n'est trouvée, chercher récursivement
            if (!$imageUrl) {
                error_log("No standard image URL format found, trying recursive search");
                $imageUrl = $this->findImageUrlInResponse($data);
                if ($imageUrl) {
                    error_log("Found image URL through recursive search: " . substr($imageUrl, 0, 50) . "...");
                }
            }
            
            // Si on a trouvé une URL d'image
            if ($imageUrl) {
                return [
                    'success' => true,
                    'image_url' => $imageUrl,
                ];
            }
            
            // Si aucune URL d'image n'a été trouvée, renvoyer une erreur
            return [
                'success' => false,
                'error' => 'Format de réponse inattendu: URL d\'image introuvable',
                'data' => $data
            ];
        } catch (\Exception $e) {
            error_log("Exception in EdenAIService: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erreur lors de la génération: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }
    
    /**
     * Recherche récursivement une URL d'image dans la réponse
     */
    private function findImageUrlInResponse($data, $currentPath = ''): ?string
    {
        if (is_array($data)) {
            // First look for common URL patterns in direct children
            foreach ($data as $key => $value) {
                // Check for URLs or base64 data in string values
                if (is_string($value)) {
                    // If it's a URL
                    if (preg_match('/^https?:\/\//i', $value)) {
                        error_log("Found URL in response at $currentPath.$key: $value");
                        return $value;
                    }
                    // If it's a base64 image
                    if (strpos($value, 'data:image') === 0) {
                        error_log("Found base64 image in response at $currentPath.$key");
                        return $value;
                    }
                }
            }
            
            // Search for image-related keys
            foreach ($data as $key => $value) {
                $newPath = $currentPath ? $currentPath . '.' . $key : $key;
                
                // Look for image-related keys
                $imageRelatedKeys = [
                    'image', 'url', 'img', 'image_url', 'image_resource_url', 
                    'b64_json', 'results', 'images', 'output', 'uri', 'link'
                ];
                
                // If the key name suggests it contains an image
                if (is_string($key) && $this->keyContainsImageTerms($key, $imageRelatedKeys)) {
                    // If it's a string value that looks like an URL or base64 data
                    if (is_string($value)) {
                        if (preg_match('/^https?:\/\//i', $value) || strpos($value, 'data:image') === 0) {
                            error_log("Found image at $newPath: $value");
                            return $value;
                        }
                    } 
                    // If it's a base64 string without the data:image prefix
                    elseif (is_string($value) && preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $value)) {
                        error_log("Found potential base64 data at $newPath");
                        return 'data:image/png;base64,' . $value;
                    }
                }
                
                // Continue searching recursively
                $result = $this->findImageUrlInResponse($value, $newPath);
                if ($result) {
                    return $result;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Check if a key name contains image-related terms
     */
    private function keyContainsImageTerms(string $key, array $terms): bool
    {
        $key = strtolower($key);
        foreach ($terms as $term) {
            if (strpos($key, $term) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Télécharge une image à partir d'une URL
     * 
     * @param string $url L'URL de l'image à télécharger
     * @return array Informations sur l'image téléchargée (contenu, nom de fichier généré)
     */
    public function downloadImage(string $url): array
    {
        try {
            error_log("Attempting to download image from: " . (strlen($url) > 50 ? substr($url, 0, 50) . '...' : $url));
            
            // Vérifier si c'est une URL data URI (base64)
            if (strpos($url, 'data:image') === 0) {
                $matches = [];
                if (preg_match('/data:image\/([a-zA-Z]+);base64,(.+)/', $url, $matches)) {
                    $extension = $matches[1];
                    $base64Data = $matches[2];
                    $content = base64_decode($base64Data);
                    
                    if ($content === false) {
                        error_log("Failed to decode base64 image data");
                        return [
                            'success' => false,
                            'error' => 'Décodage base64 échoué',
                        ];
                    }
                    
                    $filename = 'ai-generated-' . uniqid() . '.' . $extension;
                    $contentType = 'image/' . $extension;
                    
                    error_log("Successfully decoded base64 image, size: " . strlen($content) . " bytes");
                    return [
                        'success' => true,
                        'content' => $content,
                        'filename' => $filename,
                        'mime_type' => $contentType,
                    ];
                } else {
                    error_log("Invalid data URI format: " . substr($url, 0, 30) . '...');
                    return [
                        'success' => false,
                        'error' => 'Format data URI invalide',
                    ];
                }
            }
            
            // Sinon, télécharger depuis l'URL
            try {
                error_log("Downloading image from URL using HTTP client");
                $response = $this->httpClient->request('GET', $url, [
                    'timeout' => 30, // Longer timeout for image downloads
                    'max_redirects' => 5,
                    'verify_peer' => false, // Skip SSL verification if needed
                    'verify_host' => false
                ]);
                
                $statusCode = $response->getStatusCode();
                error_log("Download response status code: " . $statusCode);
                
                if ($statusCode !== 200) {
                    return [
                        'success' => false,
                        'error' => 'Erreur de téléchargement: ' . $statusCode,
                    ];
                }
                
                $content = $response->getContent();
                error_log("Downloaded image size: " . strlen($content) . " bytes");
                
                // Déterminer le type MIME de l'image
                $contentType = $response->getHeaders()['content-type'][0] ?? 'image/png';
                error_log("Content type: " . $contentType);
                
                // Si le type MIME est spécifié, extraire l'extension
                $extension = 'png'; // Par défaut
                if (strpos($contentType, 'jpeg') !== false || strpos($contentType, 'jpg') !== false) {
                    $extension = 'jpg';
                } elseif (strpos($contentType, 'webp') !== false) {
                    $extension = 'webp';
                } elseif (strpos($contentType, 'gif') !== false) {
                    $extension = 'gif';
                } elseif (strpos($contentType, 'png') !== false) {
                    $extension = 'png';
                }
                
                // Générer un nom de fichier avec la bonne extension
                $filename = 'ai-generated-' . uniqid() . '.' . $extension;
                
                return [
                    'success' => true,
                    'content' => $content,
                    'filename' => $filename,
                    'mime_type' => $contentType,
                ];
            } catch (\Exception $e) {
                // Try using file_get_contents as a fallback
                error_log("HTTP client failed, trying file_get_contents as fallback");
                $content = @file_get_contents($url);
                
                if ($content === false) {
                    throw new \Exception("file_get_contents also failed: " . error_get_last()['message'] ?? 'Unknown error');
                }
                
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $contentType = $finfo->buffer($content) ?: 'image/png';
                
                // Extract extension from content type
                $extension = 'png';
                if (strpos($contentType, 'jpeg') !== false || strpos($contentType, 'jpg') !== false) {
                    $extension = 'jpg';
                } elseif (strpos($contentType, 'webp') !== false) {
                    $extension = 'webp';
                } elseif (strpos($contentType, 'gif') !== false) {
                    $extension = 'gif';
                }
                
                $filename = 'ai-generated-' . uniqid() . '.' . $extension;
                error_log("Downloaded using file_get_contents, size: " . strlen($content) . " bytes");
                
                return [
                    'success' => true,
                    'content' => $content,
                    'filename' => $filename,
                    'mime_type' => $contentType,
                ];
            }
        } catch (\Exception $e) {
            error_log("Exception in downloadImage: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return [
                'success' => false,
                'error' => 'Erreur lors du téléchargement: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }
} 