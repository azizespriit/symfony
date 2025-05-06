<?php

// Test script for Eden AI image generation
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoiMzk1NmU2ZGUtODFkYi00NGZmLWE0YjAtZGRjNDY1OTgyMzlkIiwidHlwZSI6ImFwaV90b2tlbiJ9.51k_V4hEIovG3pHESVAYKKbzDUJYupz12N5HlYPWU64';

// API endpoint
$url = 'https://api.edenai.run/v2/image/generation';

// Request data
$data = [
    'providers' => 'openai',
    'text' => 'A beautiful modern product on a white background',
    'resolution' => '1024x1024',
    'num_images' => 1
];

// Set up cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);

// Execute the request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Output the results to a log file
$logFile = 'edenai_test_results.log';
file_put_contents($logFile, "HTTP Code: $httpCode\n\n");
file_put_contents($logFile, "Response:\n$response\n\n", FILE_APPEND);

// Parse the JSON response
$jsonResponse = json_decode($response, true);
file_put_contents($logFile, "Parsed Response:\n" . print_r($jsonResponse, true) . "\n\n", FILE_APPEND);

// Try to extract the image URL
$imageUrl = null;

// Look for common OpenAI response patterns
if (isset($jsonResponse['openai'])) {
    $openaiData = $jsonResponse['openai'];
    
    // Check for error
    if (isset($openaiData['error'])) {
        file_put_contents($logFile, "OpenAI Error: " . print_r($openaiData['error'], true) . "\n\n", FILE_APPEND);
    }
    
    // Check for results array
    if (isset($openaiData['results']) && is_array($openaiData['results']) && !empty($openaiData['results'])) {
        $result = $openaiData['results'][0];
        
        if (isset($result['url'])) {
            $imageUrl = $result['url'];
        } elseif (isset($result['image_url'])) {
            $imageUrl = $result['image_url'];
        } elseif (isset($result['b64_json'])) {
            // Handle base64 encoded image
            $imageUrl = "base64 data found";
            $base64Data = $result['b64_json'];
            
            // Save base64 as an image
            $imageData = base64_decode($base64Data);
            file_put_contents('generated_image.png', $imageData);
            file_put_contents($logFile, "Saved base64 image to generated_image.png\n", FILE_APPEND);
        }
    }
    
    // Check for items array
    if (!$imageUrl && isset($openaiData['items']) && is_array($openaiData['items']) && !empty($openaiData['items'])) {
        $item = $openaiData['items'][0];
        
        if (isset($item['image_resource_url'])) {
            $imageUrl = $item['image_resource_url'];
        } elseif (isset($item['image'])) {
            $imageUrl = $item['image'];
        }
    }
}

file_put_contents($logFile, "Image URL: " . ($imageUrl ? $imageUrl : "Not found") . "\n", FILE_APPEND);

echo "Test completed. Results saved to $logFile\n";
if ($imageUrl) {
    echo "Image URL found: " . (is_string($imageUrl) ? substr($imageUrl, 0, 50) . '...' : $imageUrl) . "\n";
} else {
    echo "No image URL found in the response.\n";
} 