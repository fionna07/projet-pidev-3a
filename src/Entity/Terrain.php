<?php

namespace App\Entity;

use App\Repository\TerrainRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TerrainRepository::class)]
class Terrain
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column]
    private ?float $surface = null;

    #[ORM\Column(length: 255)]
    private ?string $typeSol = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?float $latitude = null;

    #[ORM\Column]
    private ?float $longitude = null;

    #[ORM\ManyToOne(inversedBy: 'agriculteur')]
    private ?Utilisateur $proprietaire = null;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'terrain')]
    private Collection $terrain;

    public function __construct()
    {
        $this->terrain = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getSurface(): ?float
    {
        return $this->surface;
    }

    public function setSurface(float $surface): static
    {
        $this->surface = $surface;

        return $this;
    }

    public function getTypeSol(): ?string
    {
        return $this->typeSol;
    }

    public function setTypeSol(string $typeSol): static
    {
        $this->typeSol = $typeSol;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getProprietaire(): ?Utilisateur
    {
        return $this->proprietaire;
    }

    public function setProprietaire(?Utilisateur $proprietaire): static
    {
        $this->proprietaire = $proprietaire;

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTerrain(): Collection
    {
        return $this->terrain;
    }

    public function addTerrain(Transaction $terrain): static
    {
        if (!$this->terrain->contains($terrain)) {
            $this->terrain->add($terrain);
            $terrain->setTerrain($this);
        }

        return $this;
    }

    public function removeTerrain(Transaction $terrain): static
    {
        if ($this->terrain->removeElement($terrain)) {
            // set the owning side to null (unless already changed)
            if ($terrain->getTerrain() === $this) {
                $terrain->setTerrain(null);
            }
        }

        return $this;
    }
}
