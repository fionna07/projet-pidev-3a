<?php

namespace App\Controller;

use App\Entity\Equipement;
use App\Form\EquipementType;
use App\Repository\EquipementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/equipement')]
class EquipementController extends AbstractController
{
    #[Route('/', name: 'app_equipement')]
    public function index(Request $request, EntityManagerInterface $entityManager, EquipementRepository $equipementRepository, SluggerInterface $slugger): Response
    {
        $equipement = new Equipement();
        $form = $this->createForm(EquipementType::class, $equipement);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('equipements_directory'),
                        $newFilename
                    );
                    $equipement->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image');
                }
            }

            $entityManager->persist($equipement);
            $entityManager->flush();
            
            $this->addFlash('success', 'Équipement ajouté avec succès!');
            return $this->redirectToRoute('app_equipement');
        }

        return $this->render('equipement/indexBack.html.twig', [
            'equipements' => $equipementRepository->findAll(),
            'form' => $form->createView(),
            'modal_open' => $form->isSubmitted() && !$form->isValid(),
        ]);
    }

    #[Route('/edit/{id}', name: 'equipement_edit')]
    public function edit(Request $request, Equipement $equipement, EntityManagerInterface $entityManager, ValidatorInterface $validator, SluggerInterface $slugger): Response
    {
        try {
            // Validate required fields
            if (!$request->request->get('nom') || 
                !$request->request->get('prix') || 
                !$request->request->get('quantite_disponible') || 
                !$request->request->get('categorie')) {
                throw new \InvalidArgumentException('Tous les champs sont obligatoires.');
            }

            // Validate and sanitize input
            $nom = trim($request->request->get('nom'));
            $prix = (float)$request->request->get('prix');
            $quantite = (int)$request->request->get('quantite_disponible');
            $categorie = trim($request->request->get('categorie'));

            // Validate nom
            if (strlen($nom) < 2 || strlen($nom) > 255 || !preg_match('/^[a-zA-ZÀ-ÿ0-9\s\-_]+$/', $nom)) {
                throw new \InvalidArgumentException('Le nom doit contenir entre 2 et 255 caractères et ne peut contenir que des lettres, chiffres, espaces et tirets.');
            }

            // Validate prix
            if ($prix <= 0) {
                throw new \InvalidArgumentException('Le prix doit être supérieur à 0.');
            }

            // Validate quantite
            if ($quantite < 0) {
                throw new \InvalidArgumentException('La quantité ne peut pas être négative.');
            }

            // Validate categorie
            if (strlen($categorie) < 2 || strlen($categorie) > 255 || !preg_match('/^[a-zA-ZÀ-ÿ\s\-_]+$/', $categorie)) {
                throw new \InvalidArgumentException('La catégorie doit contenir entre 2 et 255 caractères et ne peut contenir que des lettres, espaces et tirets.');
            }

            // Handle image upload if present
            $imageFile = $request->files->get('image');
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    // Delete old image if it exists
                    if ($equipement->getImage()) {
                        $oldImagePath = $this->getParameter('equipements_directory').'/'.$equipement->getImage();
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }

                    $imageFile->move(
                        $this->getParameter('equipements_directory'),
                        $newFilename
                    );
                    $equipement->setImage($newFilename);
                } catch (FileException $e) {
                    throw new \Exception('Une erreur est survenue lors du téléchargement de l\'image.');
                }
            }

            // Update equipement
            $equipement->setNom($nom);
            $equipement->setPrix($prix);
            $equipement->setQuantiteDisponible($quantite);
            $equipement->setCategorie($categorie);

            // Validate entity
            $errors = $validator->validate($equipement);
            if (count($errors) > 0) {
                throw new \InvalidArgumentException((string) $errors);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Équipement modifié avec succès.');

        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la modification de l\'équipement.');
        }

        return $this->redirectToRoute('app_equipement');
    }

    #[Route('/delete/{id}', name: 'equipement_delete')]
    public function delete(Request $request, Equipement $equipement, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($equipement);
        $entityManager->flush();

        $this->addFlash('success', 'Équipement supprimé avec succès!');
        return $this->redirectToRoute('app_equipement');
    }
}
