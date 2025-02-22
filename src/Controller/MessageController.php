<?php
namespace App\Controller;
use App\Entity\Utilisateur;  

use App\Entity\Message;
use App\Entity\Conversation;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Route pour envoyer un message (AJAX)
    #[Route('/message/send/{conversationId}', name: 'send_message', methods: ['POST'])]
    public function sendMessage(Request $request, int $conversationId, ConversationRepository $conversationRepository): JsonResponse
    {
        // Récupérer la conversation
        $conversation = $conversationRepository->find($conversationId);
        if (!$conversation) {
            return new JsonResponse(['error' => 'Conversation introuvable'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que l'utilisateur est un participant de la conversation
        $user = $this->getUser();
        if (!$conversation->getParticipants()->contains($user)) {
            return new JsonResponse(['error' => 'Accès interdit'], Response::HTTP_FORBIDDEN);
        }

        // Récupérer le contenu du message
        $data = json_decode($request->getContent(), true);
        $messageContent = $data['content'] ?? '';
        if (empty($messageContent)) {
            return new JsonResponse(['error' => 'Le message ne peut pas être vide'], Response::HTTP_BAD_REQUEST);
        }

        // Créer et persister le message
        $message = new Message();
        $message->setSender($user);
        $message->setConversation($conversation);
        $message->setContent($messageContent);
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        // Récupérer tous les messages de la conversation
        $messages = $conversation->getMessages();
        $messageData = [];
        foreach ($messages as $message) {
            $messageData[] = [
                'sender' => $message->getSender()->getFirstname(),
                'content' => $message->getContent(),
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }

        return new JsonResponse(['success' => 'Message envoyé', 'messages' => $messageData]);
    }


   
      #[Route('/message/send/farmer/{conversationId}', name:"send_message_farmer", methods: ["POST"])]
     
    public function sendMessageFromFarmer(
        Request $request,
        ConversationRepository $conversationRepository,
        int $conversationId
    ): JsonResponse {
        // Récupérer la conversation
        $conversation = $conversationRepository->find($conversationId);
        if (!$conversation) {
            return new JsonResponse(['error' => 'Conversation introuvable'], Response::HTTP_NOT_FOUND);
        }

        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }
        
        if (!$conversation->getParticipants()->contains($user)) {
            return new JsonResponse(['error' => 'Accès interdit'], Response::HTTP_FORBIDDEN);
        }
        
        $data = json_decode($request->getContent(), true);
        $messageContent = $data['content'] ?? '';
        if (empty($messageContent)) {
            return new JsonResponse(['error' => 'Le message ne peut pas être vide'], Response::HTTP_BAD_REQUEST);
        }
        
        // Dans ce cas, l'agriculteur envoie le message vers le client,
        // le destinataire est l'autre participant.
        $participants = $conversation->getParticipants()->toArray();
        $receiver = ($participants[0] === $user) ? $participants[1] : $participants[0];

        $message = new Message();
        $message->setSender($user);
        $message->setReceiver($receiver);
        $message->setConversation($conversation);
        $message->setContent($messageContent);
        
        $this->entityManager->persist($message);
        $this->entityManager->flush();
        
        return new JsonResponse(['success' => 'Message envoyé'], Response::HTTP_OK);
    }
}
