<?php
namespace App\Service;

use Cloudinary\Cloudinary;
use Symfony\Component\HttpFoundation\File\UploadedFile; // Importer UploadedFile
class ImageUploader
{
    private $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $_ENV['CLOUDINARY_CLOUD_NAME'],
                'api_key' => $_ENV['CLOUDINARY_API_KEY'],
                'api_secret' => $_ENV['CLOUDINARY_API_SECRET'],
            ],
        ]);
    }

    public function upload(UploadedFile $file): string
    {
        // Nettoyer le chemin du fichier
        $cleanPath = str_replace("\0", '', $file->getPathname());
    
        // Lire le contenu du fichier
        $content = file_get_contents($cleanPath);
    
        // Encoder le contenu en base64
        $base64Content = base64_encode($content);
    
        // Envoyer le contenu encodé à Cloudinary
        $result = $this->cloudinary->uploadApi()->upload("data:image/jpeg;base64,$base64Content", [
            'folder' => 'profile_pictures',
            'resource_type' => 'image',
        ]);
    
        return $result['secure_url'];
    }
}