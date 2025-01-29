<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'passager')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Trajets $trajets = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'passager')]
    private ?Trajetsencours $trajetsencours = null;

    #[ORM\ManyToOne(inversedBy: 'passager')]
    private ?Trajetsfini $trajetfini = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTrajets(): ?Trajets
    {
        return $this->trajets;
    }

    public function setTrajets(?Trajets $trajets): static
    {
        $this->trajets = $trajets;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getTrajetsencours(): ?Trajetsencours
    {
        return $this->trajetsencours;
    }

    public function setTrajetsencours(?Trajetsencours $trajetsencours): static
    {
        $this->trajetsencours = $trajetsencours;

        return $this;
    }

    public function getTrajetfini(): ?Trajetsfini
    {
        return $this->trajetfini;
    }

    public function setTrajetfini(?Trajetsfini $trajetfini): static
    {
        $this->trajetfini = $trajetfini;

        return $this;
    }
}
