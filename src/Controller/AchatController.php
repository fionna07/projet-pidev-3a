<?php

namespace App\Controller;

use App\Entity\Achat;
use App\Form\AchatType;
use App\Repository\AchatRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/achat')]
class AchatController extends AbstractController
{
 
    #[Route('/', name: 'app_achat_index', methods: ['GET', 'POST'])]
    public function new(Request $request, AchatRepository $achatRepository, UtilisateurRepository $utilisateurRepository, EntityManagerInterface $entityManager): Response
    {
        $achat = new Achat();
        $form = $this->createForm(AchatType::class, $achat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($achat);
            $entityManager->flush();

            return $this->redirectToRoute('app_achat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('achat/indexback.html.twig', [
            'achats' => $achatRepository->findAll(),
            'utilisateurs' => $utilisateurRepository->findAll(),
            'form' => $form,
            'modal_open' => $form->isSubmitted() && !$form->isValid(),
        ]);
    }
    #[Route('/{id}/edit', name: 'achat_edit', methods: ['POST'])]
    public function edit(
        Request $request,
        Achat $achat,
        EntityManagerInterface $entityManager,
        UtilisateurRepository $utilisateurRepository,
        ValidatorInterface $validator
    ): Response {
        try {
         
            if (!$request->request->get('date') || !$request->request->get('total') || !$request->request->get('utilisateur')) {
                throw new \InvalidArgumentException('Tous les champs sont obligatoires.');
            }

            $utilisateur = $utilisateurRepository->find($request->request->get('utilisateur'));
            if (!$utilisateur) {
                throw new \InvalidArgumentException('Utilisateur non trouvé.');
            }

            $date = new \DateTimeImmutable($request->request->get('date'));
            $total = (float)$request->request->get('total');

            if ($total <= 0) {
                throw new \InvalidArgumentException('Le total doit être supérieur à 0.');
            }


            $achat->setDate($date);
            $achat->setTotal($total);
            $achat->setUtilisateur($utilisateur);

            $errors = $validator->validate($achat);
            if (count($errors) > 0) {
                throw new \InvalidArgumentException((string) $errors);
            }

            $entityManager->flush();
            $this->addFlash('success', 'L\'achat a été modifié avec succès.');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la modification de l\'achat.');
        }

        return $this->redirectToRoute('achat_index');
    }

    #[Route('/{id}/delete', name: 'achat_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Achat $achat,
        EntityManagerInterface $entityManager
    ): Response {
        try {
            $entityManager->remove($achat);
            $entityManager->flush();
            $this->addFlash('success', 'L\'achat a été supprimé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression de l\'achat.');
        }

        return $this->redirectToRoute('achat_index');
    }
}
