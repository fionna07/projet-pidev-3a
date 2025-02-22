<?php
namespace App\Controller;
use App\Entity\Utilisateur;  

use App\Entity\Conversation;
use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ConversationController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

 // Méthode pour afficher toutes les conversations de l'agriculteur
 #[Route('/conversations', name: 'conversations_list', methods: ['GET'])]
 public function listConversations(ConversationRepository $conversationRepository): Response
 {
     // Récupérer l'utilisateur connecté
     $user = $this->getUser();
     
     // Récupérer toutes les conversations où l'agriculteur est participant
     $conversations = $conversationRepository->findByParticipant($user); // Assurez-vous d'avoir une méthode 'findByParticipant' dans le repository

     return $this->render('conversation/conversations_list.html.twig', [
         'conversations' => $conversations
     ]);
 }

    // Route pour afficher la conversation
    #[Route('/conversation/{id}', name: 'conversation_view', methods: ['GET'])]
    public function viewConversation(Conversation $conversation, Request $request): Response
    {
        // Récupérer l'utilisateur actuel (expéditeur du message)
        $user = $this->getUser();

        // Créer un objet Message et un formulaire associé
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);

        // Traiter la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Assigner l'expéditeur et la conversation
            $message->setSender($user);
            $message->setConversation($conversation);

            // Persister le message
            $this->entityManager->persist($message);
            $this->entityManager->flush();

            // Rediriger pour afficher à nouveau la conversation après l'envoi
            return $this->redirectToRoute('conversation_view', ['id' => $conversation->getId()]);
        }

        // Passer le formulaire à la vue
        return $this->render('conversation/conversation.html.twig', [
            'conversation' => $conversation,
            'messages' => $conversation->getMessages(),
            'form' => $form->createView(),
        ]);
    }

    // Route pour démarrer une conversation
    #[Route('/conversation/start/{ownerId}', name: 'start_conversation', methods: ['POST'])]
    public function startConversation(
        Request $request,
        EntityManagerInterface $entityManager,
        ConversationRepository $conversationRepository,
        int $ownerId
    ): Response {
        // Récupérer le client (l'utilisateur actuel)
        $client = $this->getUser();
        
        // Récupérer le propriétaire (l'agriculteur)
        $owner = $entityManager->getRepository(Utilisateur::class)->find($ownerId);
        
        if (!$client || !$owner) {
            return new JsonResponse(['error' => 'Utilisateur introuvable'], Response::HTTP_NOT_FOUND);
        }
        
        // Vérifier s'il existe déjà une conversation entre ces deux utilisateurs
        $conversation = $conversationRepository->findExistingConversation($client, $owner);
        
        if (!$conversation) {
            // Si la conversation n'existe pas, créer une nouvelle conversation
            $conversation = new Conversation();
            $conversation->addParticipant($client);
            $conversation->addParticipant($owner);
            $entityManager->persist($conversation);
            $entityManager->flush();
        }
        
        // Retourner un JSON avec l'ID de la conversation
        return new JsonResponse(['conversationId' => $conversation->getId()]);
    }

    #[Route('/conversation/{conversationId}/messages', name: 'get_message', methods: ['GET'])]
    public function getMessages(int $conversationId, ConversationRepository $conversationRepository): JsonResponse
    {
        $conversation = $conversationRepository->find($conversationId);
        if (!$conversation) {
            return new JsonResponse(['error' => 'Conversation introuvable'], 404);
        }

        $messages = $conversation->getMessages();
        $messageData = [];
        foreach ($messages as $message) {
            $messageData[] = [
                'sender' => $message->getSender()->getFirstname(),
                'content' => $message->getContent(),
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }

        return new JsonResponse(['messages' => $messageData]);
    }
}
