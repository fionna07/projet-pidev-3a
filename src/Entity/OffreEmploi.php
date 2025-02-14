<?php

namespace App\Entity;

use App\Repository\OffreEmploiRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: OffreEmploiRepository::class)]
class OffreEmploi
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre ne peut pas être vide.")]
    #[Assert\Length(
        max: 100,
        maxMessage: "Le titre ne doit pas dépasser 100 caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[A-Za-zÀ-ÿ' ]+$/",
        message: "Le titre ne doit contenir que des lettres, espaces et apostrophes."
    )]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    #[Assert\Length(
        min: 10,
        minMessage: "La description doit contenir au moins 10 caractères."
    )]
    #[Assert\Regex(
        pattern: "/^(?![\d\s]+$)[A-Za-z0-9À-ÿ,.' ]+$/",
        message: "La description doit contenir au moins trois mots et ne doit pas être uniquement composée de chiffres."
    )]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le nombre de postes est obligatoire.")]
    #[Assert\GreaterThan(
        value: 2,
        message: "Le nombre de postes doit être supérieur à 2."
    )]
    private ?int $nombrePostes = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "La date de début est obligatoire.")]
    #[Assert\GreaterThan(
        "today",
        message: "La date de début doit être supérieure à la date actuelle."
    )]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "La date de fin estimée est obligatoire.")]
    #[Assert\GreaterThan(
        propertyPath: "dateDebut",
        message: "La date de fin estimée doit être supérieure à la date de début."
    )]
    private ?\DateTimeInterface $dateFinEstimee = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le statut est obligatoire.")]
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La localisation est obligatoire.")]
    private ?string $localisation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datePublication = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Les compétences requises sont obligatoires.")]
    #[Assert\Regex(
        pattern: "/.+,.+/",
        message: "Il doit y avoir au moins deux compétences séparées par une virgule."
    )]
    private ?string $competencesRequises = null;


    #[ORM\Column]
    #[Assert\NotNull(message: "Le salaire est obligatoire.")]
    #[Assert\GreaterThan(
        value: 0,
        message: "Le salaire doit être supérieur à zéro."
    )]
    private ?float $salaire = null;
    #[ORM\ManyToOne(inversedBy: 'offreEmplois')]
    private ?Utilisateur $user = null;
    
    #[ORM\OneToMany(mappedBy: "offre", targetEntity: Candidature::class, cascade: ['remove'])]
    private Collection $candidatures;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

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

    public function getNombrePostes(): ?int
    {
        return $this->nombrePostes;
    }

    public function setNombrePostes(int $nombrePostes): static
    {
        $this->nombrePostes = $nombrePostes;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFinEstimee(): ?\DateTimeInterface
    {
        return $this->dateFinEstimee;
    }

    public function setDateFinEstimee(\DateTimeInterface $dateFinEstimee): static
    {
        $this->dateFinEstimee = $dateFinEstimee;

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

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): static
    {
        $this->localisation = $localisation;

        return $this;
    }

    public function getDatePublication(): ?\DateTimeInterface
    {
        return $this->datePublication;
    }

    public function setDatePublication(\DateTimeInterface $datePublication): static
    {
        $this->datePublication = $datePublication;

        return $this;
    }

    public function getCompetencesRequises(): ?string
    {
        return $this->competencesRequises;
    }

    public function setCompetencesRequises(string $competencesRequises): static
    {
        $this->competencesRequises = $competencesRequises;

        return $this;
    }

    public function getSalaire(): ?float
    {
        return $this->salaire;
    }

    public function setSalaire(float $salaire): static
    {
        $this->salaire = $salaire;

        return $this;
    }

    public function getUser(): ?Utilisateur
    {
        return $this->user;
    }

    public function setUser(?Utilisateur $user): static
    {
        $this->user = $user;

        return $this;
    }
}
