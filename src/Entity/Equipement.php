<?php

namespace App\Entity;

use App\Repository\EquipementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EquipementRepository::class)]
class Equipement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le champ nom est obligatoire')]
    private ?string $nom = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le champ prix est obligatoire')]
    #[Assert\Positive(message: 'Le prix doit être supérieur à 0')]
    private ?float $prix = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le champ quantite_disponible est obligatoire')]
    #[Assert\Positive(message: 'La quantite disponible doit être supérieure à 0')]
    private ?int $quantite_disponible = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'Le champ image est obligatoire', groups: ['image_empty'])]
    private ?string $image = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le champ categorie est obligatoire')]
    private ?string $categorie = null;

    #[ORM\ManyToOne(inversedBy: 'equipements_fournis')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: 'Le champ fournisseur est obligatoire')]
    private ?Utilisateur $fournisseur = null;

    /**
     * @var Collection<int, Utilisateur>
     */
    #[ORM\ManyToMany(targetEntity: Utilisateur::class, inversedBy: 'equipements')]
    private Collection $id_utilisateur;

    public function __construct()
    {
        $this->id_utilisateur = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

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

    public function getQuantiteDisponible(): ?int
    {
        return $this->quantite_disponible;
    }

    public function setQuantiteDisponible(int $quantite_disponible): static
    {
        $this->quantite_disponible = $quantite_disponible;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getFournisseur(): ?Utilisateur
    {
        return $this->fournisseur;
    }

    public function setFournisseur(?Utilisateur $fournisseur): static
    {
        $this->fournisseur = $fournisseur;

        return $this;
    }

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getRelation(): Collection
    {
        return $this->id_utilisateur;
    }

    public function addRelation(Utilisateur $relation): static
    {
        if (!$this->id_utilisateur->contains($relation)) {
            $this->id_utilisateur->add($relation);
        }

        return $this;
    }

    public function removeRelation(Utilisateur $relation): static
    {
        $this->id_utilisateur->removeElement($relation);

        return $this;
    }
}
