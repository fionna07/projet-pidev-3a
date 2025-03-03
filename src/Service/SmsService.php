<?php

namespace App\Service;

use Twilio\Rest\Client;

class SmsService
{
    private $twilio;

    public function __construct()
    {
        // Remplacez ces valeurs par vos identifiants Twilio
        $sid = 'ACe2a9ba8f3396a2215524ff6cdb52bc40';
        $token = 'a7ec909b34849c3ebc7fd862421dd87c';
        $this->twilio = new Client($sid, $token);
    }

    public function sendSms($phoneNumber, $message): bool
    {
        try {
            $this->twilio->messages->create(
                $phoneNumber, // Numéro de téléphone
                [
                    'from' => '+13176085284', // Numéro Twilio
                    'body' => $message, // Message SMS
                ]
            );
            return true; // SMS envoyé avec succès
        } catch (\Exception $e) {
            // Logger l'erreur
            return false; // En cas d'erreur
        }
    }
}