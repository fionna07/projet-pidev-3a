<?php
namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Le champ 'type' doit être 'vente' ou 'location'
    #[ORM\Column(type: "string", length: 50)]
    #[Assert\NotBlank(message: "Le type de la transaction ne peut pas être vide.")]
    #[Assert\Choice(
        choices: ["vente", "location"],
        message: "Le type de la transaction doit être 'vente' ou 'location'."
    )]
    private ?string $type = null;

    // Date de la transaction, doit être dans le futur
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date de la transaction ne peut pas être vide.")]
    #[Assert\Type("\DateTimeInterface", message: "La date doit être un objet DateTime valide.")]
    #[Assert\GreaterThan("today", message: "La date de la transaction doit être dans le futur.")]
    private ?\DateTimeInterface $dateTransaction = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le montant de la transaction ne peut pas être vide.")]
    #[Assert\Type(type: "numeric", message: "Le montant doit être un nombre valide.")]
    #[Assert\Positive(message: "Le montant doit être positif.")]
    private ?float $montant = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    
    private ?Utilisateur $client = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    
    private ?Utilisateur $agriculteur = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    
    private ?Terrain $terrain = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    // Getter pour récupérer le 'type' comme une chaîne de caractères
    public function getType(): ?string
    {
        return $this->type;
    }

    // Setter pour définir le 'type' en tant que chaîne
    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getDateTransaction(): ?\DateTimeInterface
    {
        return $this->dateTransaction;
    }

    public function setDateTransaction(\DateTimeInterface $dateTransaction): static
    {
        $this->dateTransaction = $dateTransaction;
        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant;
        return $this;
    }

    public function getClient(): ?Utilisateur
    {
        return $this->client;
    }

    public function setClient(?Utilisateur $client): static
    {
        $this->client = $client;
        return $this;
    }

    public function getAgriculteur(): ?Utilisateur
    {
        return $this->agriculteur;
    }

    public function setAgriculteur(?Utilisateur $agriculteur): static
    {
        $this->agriculteur = $agriculteur;
        return $this;
    }

    public function getTerrain(): ?Terrain
    {
        return $this->terrain;
    }

    public function setTerrain(?Terrain $terrain): static
    {
        $this->terrain = $terrain;
        return $this;
    }
}
