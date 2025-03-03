<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class predictService
{
    private $flaskApiUrl;

    public function __construct(string $flaskApiUrl)
    {
        $this->flaskApiUrl = $flaskApiUrl;
    }

    public function predict(float $surface, string $typeSol, string $adresse): float
    {
        $client = HttpClient::create();

        try {
            $response = $client->request('POST', $this->flaskApiUrl . '/predict', [
                'json' => [
                    'surface' => $surface,
                    'typeSol' => $typeSol,
                    'adresse' => $adresse,
                ],
            ]);
            dump($response->getContent());
            $content = $response->toArray();
            return $content['prediction'];
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException('Erreur lors de la prédiction : ' . $e->getMessage());
        }
    }
}