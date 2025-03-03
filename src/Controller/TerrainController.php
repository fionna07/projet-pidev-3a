<?php

namespace App\Controller;

use App\Entity\Terrain;
use App\Form\TerrainType;
use App\Repository\TerrainRepository;
use App\Repository\UtilisateurRepository;
use App\Service\predictService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/terrain')]
class TerrainController extends AbstractController
{
    private $predictService;

    public function __construct(predictService $predictService)
    {
        $this->predictService = $predictService;
    }

    // Afficher la liste des terrains (Back-office)
    #[Route('/affich', name: 'app_terrain_index', methods: ['GET'])]
    public function index(TerrainRepository $terrainRepository): Response
    {
        return $this->render('terrain/ajoutback.html.twig', [
            'terrains' => $terrainRepository->findAll(),
        ]);
    }

    // Afficher la carte avec OpenLayers
    #[Route('/map', name: 'app_terrain_map', methods: ['GET'])]
    public function map(TerrainRepository $terrainRepository): Response
    {
        $terrains = $terrainRepository->findAll();
        $terrainData = [];

        foreach ($terrains as $terrain) {
            if ($terrain->getLatitude() !== null && $terrain->getLongitude() !== null) {
                $terrainData[] = [
                    'latitude' => $terrain->getLatitude(),
                    'longitude' => $terrain->getLongitude(),
                    'adresse'  => $terrain->getAdresse(),
                ];
            }
        }

        return $this->render('terrain/map.html.twig', [
            'terrains' => json_encode($terrainData),
        ]);
    }

    // Création d'un terrain
    #[Route('/new', name: 'app_terrain_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, UtilisateurRepository $utilisateurRepository): Response
    {
        $terrain = new Terrain();
        $form = $this->createForm(TerrainType::class, $terrain);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification des coordonnées
            if (!$terrain->getLatitude() || !$terrain->getLongitude()) {
                $terrain->setLatitude(48.8566);
                $terrain->setLongitude(2.3522);
                $this->addFlash('warning', 'Les coordonnées n\'ont pas été choisies, valeurs par défaut (Paris) utilisées.');
            }
            $user = $utilisateurRepository->findOneBy(['username' => $this->getUser()->getUserIdentifier()]);
           // $this->dump($user);
            // Gestion de l'upload d'image
            $photoFile = $form->get('photos')->getData();
            if ($photoFile) {
                $uploadsDirectory = $this->getParameter('terrain_images_directory');
                $newFilename = uniqid().'.'.$photoFile->guessExtension();
  
                try {
                    $photoFile->move($uploadsDirectory, $newFilename);
                    $terrain->setPhotos($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', "L'upload de la photo a échoué.");
                }
            }
            /*else {
                // Si le formulaire est soumis et non valide, on reste sur la même page
                // Les erreurs seront disponibles dans $form
                return $this->render('terrain/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }*/

            $terrain->Ownnn($user);

            $em->persist($terrain);
            $em->flush();
            return $this->redirectToRoute('app_terrain_index');
        }

        return $this->render('terrain/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Édition d'un terrain
    #[Route('/edit/{id}', name: 'app_terrain_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Terrain $terrain, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(TerrainType::class, $terrain);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photos')->getData();
            if ($photoFile) {
                $uploadsDirectory = $this->getParameter('terrain_images_directory');
                $newFilename = uniqid().'.'.$photoFile->guessExtension();

                try {
                    $photoFile->move($uploadsDirectory, $newFilename);
                    $terrain->setPhotos($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', "L'upload de la photo a échoué.");
                }
            }

            $em->flush();
            return $this->redirectToRoute('app_terrain_index');
        }

        return $this->render('terrain/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    // Édition d'un terrain
    #[Route('/editFront/{id}', name: 'app_terrain_editFront', methods: ['GET', 'POST'])]
    public function editFront(Request $request, Terrain $terrain, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(TerrainType::class, $terrain);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photos')->getData();
            if ($photoFile) {
                $uploadsDirectory = $this->getParameter('terrain_images_directory');
                $newFilename = uniqid().'.'.$photoFile->guessExtension();

                try {
                    $photoFile->move($uploadsDirectory, $newFilename);
                    $terrain->setPhotos($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', "L'upload de la photo a échoué.");
                }
            }

            $em->flush();
            return $this->redirectToRoute('app_terrain_front_crud');
        }

        return $this->render('terrain/editfront.html.twig', [
            'terrain'=> $terrain,
            'form' => $form->createView(),
        ]);
    }

    // Suppression d'un terrain
    #[Route('/delete/{id}', name: 'app_terrain_delete', methods: ['POST'])]
    public function delete(Request $request, Terrain $terrain, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $terrain->getId(), $request->request->get('_token'))) {
            $em->remove($terrain);
            $em->flush();
        }

        return $this->redirectToRoute('app_terrain_front_crud');
    }

    // Affichage des terrains dans le front-office//client
    #[Route('/front', name: 'terrain_front', methods: ['GET'])]
    public function front(Request $request, TerrainRepository $terrainRepository)
    {
        // Récupérer le statut du filtre depuis la requête GET
        $statusFilter = $request->query->get('status', null); // Valeur par défaut = null

        // Si un statut est sélectionné, filtrer les terrains en fonction du statut
        if ($statusFilter) {
            $terrains = $terrainRepository->findBy(['status' => $statusFilter]);
        } else {
            // Si aucun filtre, afficher tous les terrains
            $terrains = $terrainRepository->findAll();
        }

        // Passer 'statusFilter' et 'terrains' à la vue
        return $this->render('terrain/front.html.twig', [
            'terrains' => $terrains,
            'statusFilter' => $statusFilter,
        ]);
    }

    // Détails d'un terrain (Front)
    #[Route('/detail/{id}', name: 'terrain_detail', methods: ['GET'])]
    public function show(Terrain $terrain): Response
    {
        return $this->render('terrain/detail.html.twig', [
            'terrain' => $terrain,
        ]);
    }


    //agriculteur
    #[Route('/frontcrud', name: 'app_terrain_front_crud', methods: ['GET'])]
    public function frontCrud(TerrainRepository $terrainRepository): Response
    {
        return $this->render('terrain/frontcrud.html.twig', [
            'terrains' => $terrainRepository->findAll(),
        ]);
    }


    #[Route('/newfront', name: 'app_terrain_newfront', methods: ['GET', 'POST'])]
    public function newFront(Request $request, EntityManagerInterface $em): Response
    {
        $terrain = new Terrain();
        $form = $this->createForm(TerrainType::class, $terrain);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification des coordonnées
            if (!$terrain->getLatitude() || !$terrain->getLongitude()) {
                $terrain->setLatitude(48.8566);
                $terrain->setLongitude(2.3522);
                $this->addFlash('warning', 'Les coordonnées n\'ont pas été choisies, valeurs par défaut (Paris) utilisées.');
            }
    
            // Gestion de l'upload d'image
            $photoFile = $form->get('photos')->getData();
            if ($photoFile) {
                $uploadsDirectory = $this->getParameter('terrain_images_directory');
                $newFilename = uniqid() . '.' . $photoFile->guessExtension();
    
                try {
                    $photoFile->move($uploadsDirectory, $newFilename);
                    $terrain->setPhotos($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', "L'upload de la photo a échoué.");
                }
            }
    
            // Récupérer l'agriculteur connecté et l'affecter comme propriétaire du terrain
            $user = $this->getUser();
            if (!$user) {
                throw $this->createAccessDeniedException('Vous devez être connecté pour publier un terrain.');
            }
            $terrain->setOwn($user);
    
            $em->persist($terrain);
            $em->flush();
    
            // On peut soit rediriger en passant l'id de l'agriculteur en paramètre, soit ajouter un flash message
            $this->addFlash('success', 'Terrain ajouté avec succès par l\'agriculteur (id: ' . $user->getId() . ').');
    
            return $this->redirectToRoute('app_terrain_front_crud', ['agriculteurId' => $user->getId()]);
        }
    
        return $this->render('terrain/newfront.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/predict-price', name: 'app_terrain_predict_price', methods: ['POST'])]
    public function predictPrice(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérification des données reçues
        if (!$data || !isset($data['terrain']['surface'], $data['terrain']['typeSol'], $data['terrain']['adresse'])) {
            return new JsonResponse(['error' => 'Données invalides'], 400);
        }

        $surface = (float) $data['terrain']['surface'];
        $typeSol = $data['terrain']['typeSol'];
        $adresse = $data['terrain']['adresse']; 

        try {
            $predictedPrice = $this->predictService->predict($surface, $typeSol, $adresse);
            return new JsonResponse(['predictedPrice' => $predictedPrice]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    //filtre
    /*
    #[Route('/filter', name: 'app_terrain_filter', methods: ['GET'])]
    public function filter(Request $request, TerrainRepository $terrainRepository): Response
    {
        $status = $request->query->get('status', 'disponible'); // Valeur par défaut "disponible"
        
        $terrains = $terrainRepository->findBy(['status' => $status]);

        return $this->render('terrain/front.html.twig', [
            'terrains' => $terrains,
            'currentStatus' => $status,
        ]);
    }*/
}