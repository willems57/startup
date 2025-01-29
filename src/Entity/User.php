<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(nullable: true)]
    private ?int $credits = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'user')]
    private Collection $reservations;

    /**
     * @var Collection<int, Trajetsfini>
     */
    #[ORM\OneToMany(targetEntity: Trajetsfini::class, mappedBy: 'conducteur')]
    private Collection $conducteur;

    /**
     * @var Collection<int, Trajetsencours>
     */
    #[ORM\OneToMany(targetEntity: Trajetsencours::class, mappedBy: 'conducteur')]
    private Collection $trajetsencours;

    /**
     * @var Collection<int, Trajets>
     */
    #[ORM\OneToMany(targetEntity: Trajets::class, mappedBy: 'conducteur')]
    private Collection $trajets;

    /**
     * @var Collection<int, Voiture>
     */
    #[ORM\OneToMany(targetEntity: Voiture::class, mappedBy: 'proprietaire')]
    private Collection $voitures;

    #[ORM\Column(length: 255)]
    private ?string $apiToken = null;

    /** @throws \Exception */
    public function __construct()
    {
        $this->apiToken = bin2hex(random_bytes(60));
        $this->reservations = new ArrayCollection();
        $this->conducteur = new ArrayCollection();
        $this->trajetsencours = new ArrayCollection();
        $this->trajets = new ArrayCollection();
        $this->voitures = new ArrayCollection();
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

    /**
     * @see UserInterface
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
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

    public function getCredits(): ?int
    {
        return $this->credits;
    }

    public function setCredits(?int $credits): static
    {
        $this->credits = $credits;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setUser($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getUser() === $this) {
                $reservation->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Trajetsfini>
     */
    public function getConducteur(): Collection
    {
        return $this->conducteur;
    }

    public function addConducteur(Trajetsfini $conducteur): static
    {
        if (!$this->conducteur->contains($conducteur)) {
            $this->conducteur->add($conducteur);
            $conducteur->setConducteur($this);
        }

        return $this;
    }

    public function removeConducteur(Trajetsfini $conducteur): static
    {
        if ($this->conducteur->removeElement($conducteur)) {
            // set the owning side to null (unless already changed)
            if ($conducteur->getConducteur() === $this) {
                $conducteur->setConducteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Trajetsencours>
     */
    public function getTrajetsencours(): Collection
    {
        return $this->trajetsencours;
    }

    public function addTrajetsencour(Trajetsencours $trajetsencour): static
    {
        if (!$this->trajetsencours->contains($trajetsencour)) {
            $this->trajetsencours->add($trajetsencour);
            $trajetsencour->setConducteur($this);
        }

        return $this;
    }

    public function removeTrajetsencour(Trajetsencours $trajetsencour): static
    {
        if ($this->trajetsencours->removeElement($trajetsencour)) {
            // set the owning side to null (unless already changed)
            if ($trajetsencour->getConducteur() === $this) {
                $trajetsencour->setConducteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Trajets>
     */
    public function getTrajets(): Collection
    {
        return $this->trajets;
    }

    public function addTrajet(Trajets $trajet): static
    {
        if (!$this->trajets->contains($trajet)) {
            $this->trajets->add($trajet);
            $trajet->setConducteur($this);
        }

        return $this;
    }

    public function removeTrajet(Trajets $trajet): static
    {
        if ($this->trajets->removeElement($trajet)) {
            // set the owning side to null (unless already changed)
            if ($trajet->getConducteur() === $this) {
                $trajet->setConducteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Voiture>
     */
    public function getVoitures(): Collection
    {
        return $this->voitures;
    }

    public function addVoiture(Voiture $voiture): static
    {
        if (!$this->voitures->contains($voiture)) {
            $this->voitures->add($voiture);
            $voiture->setProprietaire($this);
        }

        return $this;
    }

    public function removeVoiture(Voiture $voiture): static
    {
        if ($this->voitures->removeElement($voiture)) {
            // set the owning side to null (unless already changed)
            if ($voiture->getProprietaire() === $this) {
                $voiture->setProprietaire(null);
            }
        }

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(string $apiToken): static
    {
        $this->apiToken = $apiToken;

        return $this;
    }
}
