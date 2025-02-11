<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\OffreEmploiRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\OffreEmploi;
use App\Form\OffreEmploiType;


class BackPagesController extends AbstractController
{
    #[Route('/back/pages', name: 'app_back_pages')]
    public function index(): Response
    {
        return $this->render('back_pages/index.html.twig', [
            'controller_name' => 'BackPagesController',
        ]);
    }
    #[Route('/back/offre', name: 'app_back_offre')]
    public function offreEmploi(OffreEmploiRepository $offreEmploiRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $offre = new OffreEmploi();
        $offre->setDatePublication(new \DateTime());
        $offre->setStatus('actif');

        $form = $this->createForm(OffreEmploiType::class, $offre);
        $form->handleRequest($request);

        $modalOpen = false; // Par défaut, le modal ne s'ouvre pas

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($offre);
                $entityManager->flush();
                return $this->redirectToRoute('app_back_offre');
            }
            $modalOpen = true; // Si erreurs, on ouvre le modal
        }

        $offres = $offreEmploiRepository->findAll();

        return $this->render('offre_emploi/indexBack.html.twig', [
            'offres' => $offres,
            'form' => $form->createView(),
            'modal_open' => $modalOpen, // On passe la variable au template
        ]);
    }
}
