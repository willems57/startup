<?php

namespace App\Entity;

use App\Repository\TrajetsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrajetsRepository::class)]
class Trajets
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $depart = null;

    #[ORM\Column(length: 255)]
    private ?string $arrive = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $duree = null;

    #[ORM\Column]
    private ?int $prix = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'trajets')]
    private Collection $passager;

    #[ORM\ManyToOne(inversedBy: 'trajets')]
    private ?User $conducteur = null;

    #[ORM\ManyToOne(inversedBy: 'trajets')]
    private ?Voiture $voiture = null;

    public function __construct()
    {
        $this->passager = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepart(): ?string
    {
        return $this->depart;
    }

    public function setDepart(string $depart): static
    {
        $this->depart = $depart;

        return $this;
    }

    public function getArrive(): ?string
    {
        return $this->arrive;
    }

    public function setArrive(string $arrive): static
    {
        $this->arrive = $arrive;

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

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getPassager(): Collection
    {
        return $this->passager;
    }

    public function addPassager(Reservation $passager): static
    {
        if (!$this->passager->contains($passager)) {
            $this->passager->add($passager);
            $passager->setTrajets($this);
        }

        return $this;
    }

    public function removePassager(Reservation $passager): static
    {
        if ($this->passager->removeElement($passager)) {
            // set the owning side to null (unless already changed)
            if ($passager->getTrajets() === $this) {
                $passager->setTrajets(null);
            }
        }

        return $this;
    }

    public function getConducteur(): ?User
    {
        return $this->conducteur;
    }

    public function setConducteur(?User $conducteur): static
    {
        $this->conducteur = $conducteur;

        return $this;
    }

    public function getVoiture(): ?Voiture
    {
        return $this->voiture;
    }

    public function setVoiture(?Voiture $voiture): static
    {
        $this->voiture = $voiture;

        return $this;
    }
}
