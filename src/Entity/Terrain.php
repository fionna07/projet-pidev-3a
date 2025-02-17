<?php

namespace App\Entity;

use App\Repository\TerrainRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TerrainRepository::class)]
class Terrain
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "L'adresse doit comporter au moins {{ limit }} caractères.",
        maxMessage: "L'adresse ne doit pas dépasser {{ limit }} caractères."
    )]
    private ?string $adresse = null;
    
    #[ORM\Column]
    #[Assert\NotBlank(message: "La surface est obligatoire.")]
    #[Assert\Positive(message: "La surface doit être supérieure à zéro.")]
    private ?float $surface = null;
    
    #[ORM\Column]
    #[Assert\NotBlank(message: "Le prix est obligatoire.")]
    #[Assert\Positive(message: "Le prix doit être supérieur à zéro.")]
    private ?float $prix = null;
    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le statut est obligatoire.")]
    #[Assert\Choice(
        choices: ['disponible', 'vendu', 'reserve'],
        message: "Le statut doit être 'disponible', 'vendu' ou 'reserve'."
    )]
    private ?string $status = null;
    
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    private ?string $description = null;
    
    #[ORM\Column]
    #[Assert\NotBlank(message: "La latitude est obligatoire.")]
    #[Assert\Range(
        min: -90,
        max: 90,
        notInRangeMessage: "La latitude doit être comprise entre {{ -90 }} et {{90 }} degrés."
    )]
    private ?float $latitude = null;
    
    #[ORM\Column]
    #[Assert\NotBlank(message: "La longitude est obligatoire.")]
    #[Assert\Range(
        min: -180,
        max: 180,
        notInRangeMessage: "La longitude doit être comprise entre {{ min }} et {{ max }} degrés."
    )]
    private ?float $longitude = null;
    
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'terrain')]
    private Collection $transactions;
    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type de sol est obligatoire.")]
    #[Assert\Choice(
        choices: ['sableux', 'argileux', 'alluvieux', 'calcaire'],
        message: "Le type de sol doit être 'sableux', 'argileux', 'alluvieux' ou 'calcaire'."
    )]
    private ?string $typeSol = null;
    
    #[ORM\Column(length: 255, nullable: true)]
    // La validation des fichiers (taille, format, etc.) sera gérée dans le formulaire.
    private ?string $photos = null;

    #[ORM\ManyToOne(inversedBy: 'terrains')]
    private ?Utilisateur $own = null;
    
    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }
    public function getOwn(): ?Utilisateur
{
    return $this->own;
}

public function setOwn(?Utilisateur $own): self
{
    $this->own = $own;
    return $this;
}


public function Ownnn(?Utilisateur $own): self
{
    $this->own = $own;
    return $this;
}

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getAdresse(): ?string
    {
        return $this->adresse;
    }
    
    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;
        return $this;
    }
    
    public function getSurface(): ?float
    {
        return $this->surface;
    }
    
    public function setSurface(float $surface): self
    {
        $this->surface = $surface;
        return $this;
    }
    
    public function getPrix(): ?float
    {
        return $this->prix;
    }
    
    public function setPrix(float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }
    
    public function getStatus(): ?string
    {
        return $this->status;
    }
    
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }
    
    public function getDescription(): ?string
    {
        return $this->description;
    }
    
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }
    
    public function getLatitude(): ?float
    {
        return $this->latitude;
    }
    
    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }
    
    public function getLongitude(): ?float
    {
        return $this->longitude;
    }
    
    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }
    
    public function getTypeSol(): ?string
    {
        return $this->typeSol;
    }
    
    public function setTypeSol(string $typeSol): self
    {
        $this->typeSol = $typeSol;
        return $this;
    }
    
    public function getPhotos(): ?string
    {
        return $this->photos;
    }
    
    public function setPhotos(?string $photos): self
    {
        $this->photos = $photos;
        return $this;
    }
    
    
    
    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }
    
    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setTerrain($this);
        }
        return $this;
    }
    
    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getTerrain() === $this) {
                $transaction->setTerrain(null);
            }
        }
        return $this;
    }
}
