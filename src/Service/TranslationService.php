<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TranslationService
{
    private $client;
    private $apiKey;
    private $apiHost;
    private $apiUrl;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->apiKey = 'ab26281f97msh97e609e3aa21b28p13025ejsn06ca9400d002'; // Ta clé API
        $this->apiHost = 'google-translation-unlimited.p.rapidapi.com';
        $this->apiUrl = "https://{$this->apiHost}/trans";
    }

    public function translateText(string $text, string $sourceLang, string $targetLang): ?string
    {
        $response = $this->client->request('POST', $this->apiUrl, [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-rapidapi-host' => $this->apiHost,
                'x-rapidapi-key' => $this->apiKey,
            ],
            'json' => [
                'from' => $sourceLang,
                'to' => $targetLang,
                'q' => $text
            ]
        ]);

        $data = $response->toArray();

        return $data['translated_text'] ?? null; // Vérifie la clé exacte dans la réponse JSON
    }
}
