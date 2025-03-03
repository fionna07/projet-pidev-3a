<?php

namespace App\Controller;
use TCPDF;
use Knp\Snappy\Pdf;
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
    public function createTransaction(Request $request, EntityManagerInterface $em, $id): Response
    {
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

        if ($form->isSubmitted() && $form->isValid()) {
            // Date de la transaction
            $transaction->setDateTransaction(new \DateTime());

            // Associer un agriculteur (exemple ID fixe)
            $agriculteur = $em->getRepository(Utilisateur::class)->find(1);
            $transaction->setAgriculteur($agriculteur);

            // Associer le terrain à la transaction
            $transaction->setTerrain($terrain);

            // Vérifier si la transaction est une vente ou une location
            if ($transaction->getType() === 'vente') {
                $terrain->setStatus('vendu');
            } elseif ($transaction->getType() === 'location') {
                $terrain->setStatus('reserve');
            }

            // Persister les entités
            $em->persist($transaction);
            $em->persist($terrain);
            $em->flush();

            // Génération du PDF avec TCPDF
            $pdf = new TCPDF();
            $pdf->AddPage();
            
            // Ajouter du contenu HTML au PDF
            $html = $this->renderView('transaction/contrat.html.twig', [
                'transaction' => $transaction,
            ]);
            
            // Charger le contenu HTML dans TCPDF
            $pdf->writeHTML($html);
            $pdf->Output('contrat_' . $transaction->getId() . '.pdf', 'D');

            return new Response('', 200);
        }

        // Affichage du formulaire
        return $this->render('transaction/transac.html.twig', [
            'form' => $form->createView(),
            'clients' => $clients,
        ]);
    }

    #[Route('/transactions/agriculteur/{id}', name: 'agriculteur_transactions')]
    public function showTransactionsForAgriculteur(int $id, EntityManagerInterface $em): Response
    {
        $agriculteur = $em->getRepository(Utilisateur::class)->find($id);
        if (!$agriculteur) {
            throw $this->createNotFoundException('Agriculteur non trouvé');
        }

        $transactions = $em->getRepository(Transaction::class)->findBy(['agriculteur' => $agriculteur]);

        return $this->render('transaction/affichtransac.html.twig', [
            'agriculteur' => $agriculteur,
            'transactions' => $transactions,
        ]);
    }

    #[Route('/transactions', name: 'all_transactions')]
    public function showAllTransactions(EntityManagerInterface $em): Response
    {
        $transactions = $em->getRepository(Transaction::class)->findAll();

        return $this->render('transaction/affichtransacback.html.twig', [
            'transactions' => $transactions,
        ]);
    }

    #[Route('/transaction/delete/{id}', name: 'app_transaction_delete')]
    public function deleteTransaction(int $id, EntityManagerInterface $em): Response
    {
        $transaction = $em->getRepository(Transaction::class)->find($id);

        if (!$transaction) {
            throw $this->createNotFoundException('Transaction non trouvée');
        }

        $em->remove($transaction);
        $em->flush();

        $this->addFlash('success', 'Transaction supprimée avec succès!');

        return $this->redirectToRoute('all_transactions');
    }
}
