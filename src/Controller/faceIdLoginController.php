<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Service\FaceAPIService;
use App\Security\UtilisateurAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;

class faceIdLoginController extends AbstractController
{
    private $security;
    private $userAuthenticator;

    public function __construct(Security $security, UtilisateurAuthenticator $userAuthenticator)
    {
        $this->security = $security;
        $this->userAuthenticator = $userAuthenticator;
    }

    #[Route('/face-login', name: 'app_face_login')]
    public function faceLogin(
        Request $request,
        FaceAPIService $faceAPIService,
        EntityManagerInterface $entityManager,
        UserAuthenticatorInterface $userAuthenticator
    ): Response {
        // Handle form submission
        if ($request->isMethod('POST')) {
            $faceImageData = $request->request->get('face_image_data');
    
            if ($faceImageData) {
                // Step 1: Decode the base64 image
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $faceImageData));
                $tempFilePath = sys_get_temp_dir() . '/captured_image.jpg';//image capture
                file_put_contents($tempFilePath, $imageData);//stocker image dans path tempo
    
                // Step 2: Detect face in the captured image
                $uploadedFaceToken = $faceAPIService->detectFace($tempFilePath);
    
                if ($uploadedFaceToken) {
                    // Step 3: Compare the face with all users' profile pictures
                    $users = $entityManager->getRepository(Utilisateur::class)->findAll();
                    $matchedUser = null;
    
                    foreach ($users as $user) {
                        $storedImageUrl = $user->getImage();
                        $storedFaceToken = null;
    
                        if (!empty($storedImageUrl)) {
                            // Vérifier si c'est une URL ou un chemin local
                            if (filter_var($storedImageUrl, FILTER_VALIDATE_URL)) {
                                // Télécharger et stocker temporairement l'image distante
                                $tempStoredFilePath = sys_get_temp_dir() . '/stored_image_' . uniqid() . '.jpg';
                                file_put_contents($tempStoredFilePath, file_get_contents($storedImageUrl));
    
                                // Vérification avant d'envoyer à detectFace()
                                if (file_exists($tempStoredFilePath) && filesize($tempStoredFilePath) > 0) {
                                    $storedFaceToken = $faceAPIService->detectFace($tempStoredFilePath);
                                    unlink($tempStoredFilePath); // Nettoyage après utilisation
                                }
                            } elseif (file_exists($storedImageUrl)) {
                                // Si c'est un chemin local, on le passe directement
                                $storedFaceToken = $faceAPIService->detectFace($storedImageUrl);
                            }
                        }
    
                        if ($storedFaceToken) {
                            $confidence = $faceAPIService->compareFaces($uploadedFaceToken, $storedFaceToken);
    
                            if ($confidence > 80) {
                                $matchedUser = $user;
                                break;
                            }
                        }
                    }
    
                    // Step 4: Log the user in if a match is found
                    if ($matchedUser) {
                        return $userAuthenticator->authenticateUser(
                            $matchedUser,
                            $this->userAuthenticator,
                            $request,
                            [new RememberMeBadge()]
                        );
                    } else {
                        $this->addFlash('error', 'Aucun visage correspondant trouvé.');
                    }
                } else {
                    $this->addFlash('error', 'Aucun visage détecté dans l\'image.');
                }
    
                // Clean up: Delete the temporary image file
                unlink($tempFilePath);
            } else {
                $this->addFlash('error', 'Aucune image capturée.');
            }
        }
    
        return $this->render('security/faceIdLogin.html.twig');
    }
    
}