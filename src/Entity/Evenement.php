<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieu = null;

    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'evenement', orphanRemoval: true)]
    private Collection $avis;

    #[ORM\ManyToMany(targetEntity: Utilisateur::class, inversedBy: 'evenementsResponsable')]
    #[ORM\JoinTable(name: 'responsable')]
    private Collection $responsables;

    public function __construct()
    {
        $this->avis = new ArrayCollection();
        $this->responsables = new ArrayCollection();
    }

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

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): static
    {
        $this->lieu = $lieu;
        return $this;
    }

    /**
     * @return Collection<int, Avis>
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvi(Avis $avi): static
    {
        if (!$this->avis->contains($avi)) {
            $this->avis->add($avi);
            $avi->setEvenement($this);
        }
        return $this;
    }

    public function removeAvi(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            if ($avi->getEvenement() === $this) {
                $avi->setEvenement(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getResponsables(): Collection
    {
        return $this->responsables;
    }

    public function addResponsable(Utilisateur $responsable): static
    {
        if (!$this->responsables->contains($responsable)) {
            $this->responsables->add($responsable);
        }
        return $this;
    }

    public function removeResponsable(Utilisateur $responsable): static
    {
        $this->responsables->removeElement($responsable);
        return $this;
    }

    /**
     * Vérifie si un utilisateur est responsable de cet événement
     */
    public function isResponsable(Utilisateur $utilisateur): bool
    {
        return $this->responsables->contains($utilisateur);
    }

    /**
     * Retourne uniquement les avis modérés (acceptés)
     */
    public function getAvisModeres(): array
    {
        $avisModeres = [];
        foreach ($this->avis as $avis) {
            if ($avis->isAccepter()) {
                $avisModeres[] = $avis;
            }
        }
        return $avisModeres;
    }

    public function __toString(): string
    {
        return $this->titre ?? 'Événement sans titre';
    }
}