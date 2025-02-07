<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column]
    private ?bool $isVerified = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $role = [];

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    /**
     * @var Collection<int, Terrain>
     */
    #[ORM\OneToMany(targetEntity: Terrain::class, mappedBy: 'proprietaire')]
    private Collection $agriculteur;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'client')]
    private Collection $client;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'agriculteur')]
    private Collection $agri;

    /**
     * @var Collection<int, OffreEmploi>
     */
    #[ORM\OneToMany(targetEntity: OffreEmploi::class, mappedBy: 'user')]
    private Collection $offreEmplois;

    public function __construct()
    {
        $this->agriculteur = new ArrayCollection();
        $this->client = new ArrayCollection();
        $this->agri = new ArrayCollection();
        $this->offreEmplois = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

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

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getRole(): array
    {
        return $this->role;
    }

    public function setRole(array $role): static
    {
        $this->role = $role;

        return $this;
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

    /**
     * @return Collection<int, Terrain>
     */
    public function getAgriculteur(): Collection
    {
        return $this->agriculteur;
    }

    public function addAgriculteur(Terrain $agriculteur): static
    {
        if (!$this->agriculteur->contains($agriculteur)) {
            $this->agriculteur->add($agriculteur);
            $agriculteur->setProprietaire($this);
        }

        return $this;
    }

    public function removeAgriculteur(Terrain $agriculteur): static
    {
        if ($this->agriculteur->removeElement($agriculteur)) {
            // set the owning side to null (unless already changed)
            if ($agriculteur->getProprietaire() === $this) {
                $agriculteur->setProprietaire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getClient(): Collection
    {
        return $this->client;
    }

    public function addClient(Transaction $client): static
    {
        if (!$this->client->contains($client)) {
            $this->client->add($client);
            $client->setClient($this);
        }

        return $this;
    }

    public function removeClient(Transaction $client): static
    {
        if ($this->client->removeElement($client)) {
            // set the owning side to null (unless already changed)
            if ($client->getClient() === $this) {
                $client->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getAgri(): Collection
    {
        return $this->agri;
    }

    public function addAgri(Transaction $agri): static
    {
        if (!$this->agri->contains($agri)) {
            $this->agri->add($agri);
            $agri->setAgriculteur($this);
        }

        return $this;
    }

    public function removeAgri(Transaction $agri): static
    {
        if ($this->agri->removeElement($agri)) {
            // set the owning side to null (unless already changed)
            if ($agri->getAgriculteur() === $this) {
                $agri->setAgriculteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OffreEmploi>
     */
    public function getOffreEmplois(): Collection
    {
        return $this->offreEmplois;
    }

    public function addOffreEmploi(OffreEmploi $offreEmploi): static
    {
        if (!$this->offreEmplois->contains($offreEmploi)) {
            $this->offreEmplois->add($offreEmploi);
            $offreEmploi->setUser($this);
        }

        return $this;
    }

    public function removeOffreEmploi(OffreEmploi $offreEmploi): static
    {
        if ($this->offreEmplois->removeElement($offreEmploi)) {
            // set the owning side to null (unless already changed)
            if ($offreEmploi->getUser() === $this) {
                $offreEmploi->setUser(null);
            }
        }

        return $this;
    }
}
