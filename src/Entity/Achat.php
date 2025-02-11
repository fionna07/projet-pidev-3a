<?php

namespace App\Entity;

use App\Repository\AchatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AchatRepository::class)]
class Achat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le champ id est obligatoire')]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le champ total est obligatoire')]
    #[Assert\Positive(message: 'Le total doit être supérieur à 0')]
    private ?int $total = null;

    #[ORM\ManyToOne(inversedBy: 'achats')]
    #[Assert\NotBlank(message: 'Le champ utilisateur est obligatoire')]
    private ?Utilisateur $utilisateur = null;

    /**
     * @var Collection<int, Equipement>
     */
    #[ORM\ManyToMany(targetEntity: Equipement::class, inversedBy: 'achats')]
    private Collection $relation_equi;

    public function __construct()
    {
        $this->date = new \DateTimeImmutable('now');
        $this->relation_equi = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(int $total): static
    {
        $this->total = $total;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * @return Collection<int, Equipement>
     */
    public function getRelationEqui(): Collection
    {
        return $this->relation_equi;
    }

    public function addRelationEqui(Equipement $relationEqui): static
    {
        if (!$this->relation_equi->contains($relationEqui)) {
            $this->relation_equi->add($relationEqui);
        }

        return $this;
    }

    public function removeRelationEqui(Equipement $relationEqui): static
    {
        $this->relation_equi->removeElement($relationEqui);

        return $this;
    }
}
