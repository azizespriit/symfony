<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeocodingService
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getCoordinatesFromPlace(string $place): ?array
    {
        $url = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($place);
        $response = $this->client->request('GET', $url);

        // Si la réponse est valide et contient des résultats
        $data = $response->toArray();

        if (empty($data)) {
            return null; // Pas de résultats
        }

        // On prend le premier résultat
        $location = $data[0];

        return [
            'latitude' => $location['lat'],
            'longitude' => $location['lon'],
        ];
    }
}
