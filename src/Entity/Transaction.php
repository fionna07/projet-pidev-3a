<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateTransaction = null;

    #[ORM\Column]
    private ?float $prixTransaction = null;

    #[ORM\Column(length: 255)]
    private ?string $etat = null;

    #[ORM\ManyToOne(inversedBy: 'terrain')]
    private ?Terrain $terrain = null;

    #[ORM\ManyToOne(inversedBy: 'client')]
    private ?Utilisateur $client = null;

    #[ORM\ManyToOne(inversedBy: 'agri')]
    private ?Utilisateur $agriculteur = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $conversation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

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

    public function getPrixTransaction(): ?float
    {
        return $this->prixTransaction;
    }

    public function setPrixTransaction(float $prixTransaction): static
    {
        $this->prixTransaction = $prixTransaction;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

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

    public function getConversation(): ?string
    {
        return $this->conversation;
    }

    public function setConversation(string $conversation): static
    {
        $this->conversation = $conversation;

        return $this;
    }
}
