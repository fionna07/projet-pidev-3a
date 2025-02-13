<?php

namespace App\Entity;
use App\Repository\UtilisateurRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
#[UniqueEntity(fields: ['email'], message: 'Un compte existe déjà avec cet email.')]

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'L\'email "{{ value }}" n\'est pas valide.')]
    private string $email;

    
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'adresse est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^\d+\s+[a-zA-Z\s]+,\s*\d{4,5}$/',
        message: 'L\'adresse doit commencer par un chiffre ou un nombre, suivie de chaînes de caractères, et se terminer par un code postal (4 ou 5 chiffres).'
    )]
    private ?string $adresse = null;


    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $password = null;
    
    #[ORM\Column(type: 'string', length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\s\-]+$/',
        message: 'Le prénom ne doit contenir que des lettres, des espaces et des tirets.'
    )]
    private ?string $firstName = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\s\-]+$/',
        message: 'Le nom ne doit contenir que des lettres, des espaces et des tirets.'
    )]
    private ?string $lastName = null;
    #[ORM\Column(type: 'date')]
    #[Assert\NotBlank(message: 'La date de naissance est obligatoire.')]
    #[Assert\LessThanOrEqual(
        value: '-18 years',
        message: 'Vous devez avoir au moins 18 ans.'
    )]
    private ?\DateTimeInterface $dateNaissance = null;


    #[ORM\Column(length: 255, nullable: true)] 
    private ?string $image = null;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(length: 255)]
    private ?string $status = 'désactivé';

    #[ORM\Column(type: 'boolean')]
    private ?bool $isVerified = false;
     
    #[ORM\Column(length:225)]
    private $confirmationToken;

    // ... autres getters et setters

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // S'assurer que ROLE_USER est toujours présent
        if (!in_array('ROLE_USER', $roles, true)) {
        $roles[] = 'ROLE_USER';
           }
           return $roles;
    
   }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;

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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

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

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

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

    #[ORM\Column(type: 'string', length: 15, unique: true)]
    #[Assert\NotBlank(message: 'Le numéro de téléphone est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^[259]\d{7}$/',
        message: 'Le numéro de téléphone doit commencer par 2, 5 ou 9 et contenir 8 chiffres.'
    )]
    private ?int $numTel = null;
    public function __construct()
    {
        $this->agriculteur = new ArrayCollection();
        $this->client = new ArrayCollection();
        $this->agri = new ArrayCollection();
        $this->offreEmplois = new ArrayCollection();
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

    public function getNumTel(): ?int
    {
        return $this->numTel;
    }

    public function setNumTel(int $numTel): static
    {
        $this->numTel = $numTel;

        return $this;
    }

}
