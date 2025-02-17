<?php
namespace App\DataFixtures;

use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création de l'utilisateur agriculteur
        $agriculteur = new Utilisateur();
        $agriculteur->setFirstName('Agriculteur')
            ->setLastName('Ben Agriculteur')
            ->setEmail('agriculteur@example.com')
            ->setRole(['ROLE_AGRICULTEUR'])  // Assurez-vous que le rôle est configuré
            ->setPassword('password123')  // Vous devrez probablement utiliser un encodeur de mot de passe dans une application réelle
            ->setDateCreation(new \DateTime())  // Date de création actuelle
            ->setStatus('actif')  // Un statut valide
            ->setVerified(true)  // Utilisateur vérifié
            ->setImage('image_agriculteur.jpg')  // Image par défaut
            ->setAdresse('Adresse de l\'Agriculteur, Tunis');  // Adresse valide

        // Création de l'utilisateur client
        $client = new Utilisateur();
        $client->setFirstName('Client')
            ->setLastName('Ben Client')
            ->setEmail('client@example.com')
            ->setRole(['ROLE_CLIENT'])  // Assurez-vous que le rôle est configuré
            ->setPassword('password123')  // Vous devrez probablement utiliser un encodeur de mot de passe dans une application réelle
            ->setDateCreation(new \DateTime())  // Date de création actuelle
            ->setStatus('actif')  // Un statut valide
            ->setVerified(true)  // Utilisateur vérifié
            ->setImage('image_client.jpg')  // Image par défaut
            ->setAdresse('Adresse du Client, Sfax');  // Adresse valide

        // Persister les utilisateurs dans la base de données
        $manager->persist($agriculteur);
        $manager->persist($client);
        
        $manager->flush();
    }
}
