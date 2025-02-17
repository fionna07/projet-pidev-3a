<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\Utilisateur;
use App\Entity\Terrain;
use App\Form\TransactionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class TransactionController extends AbstractController
{

    #[Route('/transaction/create/{id}', name: 'app_transaction_create')]
    public function createTransaction(Request $request, EntityManagerInterface $em, $id)
    {
        // Trouver tous les clients
        $clients = $em->getRepository(Utilisateur::class)->findAll();
        
        // Trouver le terrain avec l'ID passé dans l'URL
        $terrain = $em->getRepository(Terrain::class)->find($id);
        if (!$terrain) {
            throw $this->createNotFoundException('Terrain non trouvé.');
        }
        
        // Création du formulaire
        $transaction = new Transaction();
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);
    
        // Vérification si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $transaction->setDateTransaction(new \DateTime());
    
            // Récupérer l'agriculteur connecté
            $user = $this->getUser();
            $transaction->setAgriculteur($user);  // Utilisateur connecté (agriculteur)
            
            // Assigner le terrain à la transaction
            $transaction->setTerrain($terrain);
    
            $em->persist($transaction);
            $em->flush();
    
            $this->addFlash('success', 'Transaction créée avec succès!');
            return $this->redirectToRoute('app_terrain_front_crud');
        }
    
        // Passer le formulaire et la liste des clients au template
        return $this->render('transaction/transac.html.twig', [
            'form' => $form->createView(),
            'clients' => $clients,
        ]);
    }
    
    

    // Route pour afficher les transactions d'un agriculteur
    #[Route('/transactions/agriculteur/{id}', name: 'agriculteur_transactions')]
    public function showTransactionsForAgriculteur(int $id, EntityManagerInterface $em): Response
    {
        // Récupérer l'agriculteur par ID
        $agriculteur = $em->getRepository(Utilisateur::class)->find($id);

        if (!$agriculteur) {
            throw $this->createNotFoundException('Agriculteur non trouvé');
        }

        // Récupérer toutes les transactions pour cet agriculteur
        $transactions = $em->getRepository(Transaction::class)->findBy(['agriculteur' => $agriculteur]);

        // Affichage des transactions dans la vue Twig
        return $this->render('transaction/affichtransac.html.twig', [
            'agriculteur' => $agriculteur,
            'transactions' => $transactions
        ]);
    }



    
    // Route pour afficher toutes les transactions
    #[Route('/transactions', name: 'all_transactions')]
    public function showAllTransactions(EntityManagerInterface $em): Response
    {
        // Récupérer toutes les transactions
        $transactions = $em->getRepository(Transaction::class)->findAll();

        // Rendre la vue avec les transactions
        return $this->render('transaction/affichtransacback.html.twig', [
            'transactions' => $transactions
        ]);
    }




    // Route pour supprimer une transaction
#[Route('/transaction/delete/{id}', name: 'app_transaction_delete')]
public function deleteTransaction(int $id, EntityManagerInterface $em): Response
{
    // Trouver la transaction à supprimer par son ID
    $transaction = $em->getRepository(Transaction::class)->find($id);
    
    if (!$transaction) {
        throw $this->createNotFoundException('Transaction non trouvée');
    }

    // Supprimer la transaction
    $em->remove($transaction);
    $em->flush();

    // Ajouter un message flash pour indiquer que la transaction a été supprimée
    $this->addFlash('success', 'Transaction supprimée avec succès!');

    // Rediriger vers la page de toutes les transactions après la suppression
    return $this->redirectToRoute('all_transactions');
}



}
