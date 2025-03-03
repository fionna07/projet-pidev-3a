<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class predictService
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function predict(float $surface, string $typeSol, string $adresse): float
    {
        // Envoyer les données à l'API Flask
        $response = $this->httpClient->request('POST', 'http://localhost:5000/predict', [
            'json' => [
                'surface' => $surface,
                'typeSol' => $typeSol,
                'adresse' => $adresse,
            ],
        ]);

        // Décoder la réponse JSON
        $data = $response->toArray();

        // Retourner le prix prédit
        return $data['predictedPrice'];
    }
}