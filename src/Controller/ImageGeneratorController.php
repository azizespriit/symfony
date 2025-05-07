<?php

namespace App\Controller;

use App\Service\EdenAIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Psr\Log\LoggerInterface;

#[Route('/image-generator')]
class ImageGeneratorController extends AbstractController
{
    #[Route('/generate', name: 'image_generate', methods: ['POST'])]
    public function generate(Request $request, EdenAIService $edenAIService, LoggerInterface $logger): JsonResponse
    {
        // Récupérer la description depuis la requête
        $data = json_decode($request->getContent(), true);
        $description = $data['description'] ?? null;
        
        $logger->info('Image generation request received', [
            'description_length' => strlen($description)
        ]);
        
        if (!$description) {
            $logger->warning('Image generation failed: description missing');
            return $this->json([
                'success' => false,
                'error' => 'Description manquante'
            ], 400);
        }
        
        // Générer l'image via Eden AI
        $logger->info('Calling EdenAI service');
        $result = $edenAIService->generateImageFromText($description);
        
        if (!$result['success']) {
            $logger->error('Image generation failed: EdenAI service error', [
                'error' => $result['error'] ?? 'Unknown error',
                'details' => $result['details'] ?? null
            ]);
            
            // On retourne tous les détails disponibles pour faciliter le débogage
            return $this->json([
                'success' => false,
                'error' => $result['error'] ?? 'Erreur lors de la génération de l\'image',
                'details' => $result['details'] ?? null,
                'debug_info' => isset($result['data']) ? json_encode($result['data']) : null
            ], 500);
        }
        
        $logger->info('Image generated successfully, downloading image');
        
        // Télécharger l'image générée
        $imageData = $edenAIService->downloadImage($result['image_url']);
        
        if (!$imageData['success']) {
            $logger->error('Image download failed', [
                'error' => $imageData['error'] ?? 'Unknown error',
                'url' => $result['image_url']
            ]);
            
            return $this->json([
                'success' => false,
                'error' => $imageData['error'] ?? 'Erreur lors du téléchargement de l\'image',
                'url' => $result['image_url'] // Ajouter l'URL pour debug
            ], 500);
        }
        
        // Sauvegarder l'image dans le dossier uploads
        try {
            $uploadsDirectory = $this->getParameter('produit_images_directory');
            $filename = $imageData['filename'];
            
            $logger->info('Saving image to disk', [
                'directory' => $uploadsDirectory,
                'filename' => $filename
            ]);
            
            // Vérifier que le dossier existe et est accessible en écriture
            if (!is_dir($uploadsDirectory)) {
                $logger->error('Upload directory does not exist', [
                    'directory' => $uploadsDirectory
                ]);
                
                // Tenter de créer le répertoire
                if (!@mkdir($uploadsDirectory, 0777, true)) {
                    $umask = umask(0);
                    mkdir($uploadsDirectory, 0777, true);
                    umask($umask);
                }
                
                if (is_dir($uploadsDirectory)) {
                    chmod($uploadsDirectory, 0777); // Set full permissions
                    $logger->info('Upload directory created', [
                        'directory' => $uploadsDirectory
                    ]);
                } else {
                    throw new \Exception("Impossible de créer le répertoire d'upload: $uploadsDirectory");
                }
            }
            
            if (!is_writable($uploadsDirectory)) {
                $logger->error('Upload directory not writable', [
                    'directory' => $uploadsDirectory
                ]);
                
                return $this->json([
                    'success' => false,
                    'error' => 'Le dossier de destination n\'est pas accessible en écriture: ' . $uploadsDirectory
                ], 500);
            }
            
            // Sauvegarder l'image
            file_put_contents($uploadsDirectory . '/' . $filename, $imageData['content']);
            
            $logger->info('Image saved successfully');
            
            // Retourner les informations sur l'image générée
            return $this->json([
                'success' => true,
                'filename' => $filename,
                'preview_url' => $this->getParameter('uploads_base_url') . '/produits/' . $filename,
                'mime_type' => $imageData['mime_type']
            ]);
        } catch (\Exception $e) {
            $logger->error('Error saving image', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de l\'enregistrement de l\'image : ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
} 