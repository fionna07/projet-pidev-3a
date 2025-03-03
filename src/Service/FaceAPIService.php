<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class FaceAPIService
{
    private $httpClient;
    private $apiKey;
    private $apiSecret;

    public function __construct(HttpClientInterface $httpClient, string $apiKey, string $apiSecret)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    /**
     * Detects a face in the given image and returns the face token.
     */
    public function detectFace(string $imagePath): ?string
    {
     
        try {
            $response = $this->httpClient->request('POST', 'https://api-us.faceplusplus.com/facepp/v3/detect', [
                'body' => [
                    'api_key' => $this->apiKey,
                    'api_secret' => $this->apiSecret,
                    'image_file' => fopen($imagePath, 'r'),//image capturée par camera
                    'return_attributes' => 'none',
                ],
            ]);

            $data = $response->toArray();
            return $data['faces'][0]['face_token'] ?? null;
        } catch (\Exception $e) {
            dump($e->getMessage());
            return null;
        }
    }

    /**
     * Compares two face tokens and returns the confidence score.
     */
    public function compareFaces(string $faceToken1, string $faceToken2): ?float
    {
        try {
            $response = $this->httpClient->request('POST', 'https://api-us.faceplusplus.com/facepp/v3/compare', [
                'body' => [
                    'api_key' => $this->apiKey,
                    'api_secret' => $this->apiSecret,
                    'face_token1' => $faceToken1,
                    'face_token2' => $faceToken2,
                ],
            ]);

            $data = $response->toArray();
            return $data['confidence'] ?? null;
        } catch (\Exception $e) {
            dump($e->getMessage());
            return null;
        }
    }
}