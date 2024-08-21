<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[Assert\NotBlank(message: "Le champ 'nom' ne doit pas être vide.")]
    private ?string $nomUtilisateur = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[Assert\NotBlank(message: "Le champ 'prénom' ne doit pas être vide.")]
    private ?string $prenomUtilisateur = null;

    #[ORM\Column(length: 180)]
    private ?string $emailUtilisateur = null;

    #[ORM\Column(length: 14)]
    private ?string $motDePasse = null;

    /**
     * @var Collection<int, DevisStation>
     */
    #[ORM\OneToMany(targetEntity: DevisStation::class, mappedBy: 'utilisateur')]
    private Collection $devisStations;

    /**
     * @var Collection<int, DevisStationGasoil>
     */
    #[ORM\OneToMany(targetEntity: DevisStationGasoil::class, mappedBy: 'utilisateur')]
    private Collection $devisStationsGasoil;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoUrl = null;

    /**
     * @var Collection<int, Bons>
     */
    #[ORM\OneToMany(targetEntity: Bons::class, mappedBy: 'utilisateur')]
    private Collection $bons;

    /**
     * @var Collection<int, BudgetSommeTotal>
     */
    #[ORM\OneToMany(targetEntity: BudgetSommeTotal::class, mappedBy: 'utilisateur')]
    private Collection $budgetSommeTotals;

    public function __construct()
    {
        $this->devisStations = new ArrayCollection();
        $this->bons = new ArrayCollection();
        $this->budgetSommeTotals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomUtilisateur(): ?string
    {
        return $this->nomUtilisateur;
    }

    public function setNomUtilisateur(string $nomUtilisateur): static
    {
        $this->nomUtilisateur = $nomUtilisateur;

        return $this;
    }

    public function getPrenomUtilisateur(): ?string
    {
        return $this->prenomUtilisateur;
    }

    public function setPrenomUtilisateur(string $prenomUtilisateur): static
    {
        $this->prenomUtilisateur = $prenomUtilisateur;

        return $this;
    }

    public function getEmailUtilisateur(): ?string
    {
        return $this->emailUtilisateur;
    }

    public function setEmailUtilisateur(?string $emailUtilisateur): static
    {
        $this->emailUtilisateur = $emailUtilisateur;

        return $this;
    }

    public function getMotDePasse(): ?string
    {
        return $this->motDePasse;
    }

    public function setMotDePasse(?string $motDePasse): static
    {
        $this->motDePasse = $motDePasse;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->emailUtilisateur; // Utilisez l'email comme identifiant unique
    }

    public function getPassword(): ?string
    {
        return $this->motDePasse;
    }

    public function getRoles(): array
    {
        // Pour l'exemple, renvoyer un rôle par défaut
        return ['ROLE_USER'];
    }

    public function getSalt(): ?string
    {
        // Vous n'avez probablement pas besoin d'un "salt" si vous utilisez bcrypt ou sodium
        return null;
    }

    public function eraseCredentials(): void
    {
        // Effacez ici les données sensibles si nécessaire
        // *$this->plainPassword = null;
    }

    // Ajoutez la méthode getUsername si nécessaire pour rétrocompatibilité
    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    /**
     * @return Collection<int, DevisStation>
     */
    public function getDevisStations(): Collection
    {
        return $this->devisStations;
    }

    public function addDevisStation(DevisStation $devisStation): static
    {
        if (!$this->devisStations->contains($devisStation)) {
            $this->devisStations->add($devisStation);
            $devisStation->setUtilisateur($this);
        }

        return $this;
    }

    public function removeDevisStation(DevisStation $devisStation): static
    {
        if ($this->devisStations->removeElement($devisStation)) {
            // set the owning side to null (unless already changed)
            if ($devisStation->getUtilisateur() === $this) {
                $devisStation->setUtilisateur(null);
            }
        }

        return $this;
    }

    public function getPhotoUrl(): ?string
    {
        return $this->photoUrl;
    }

    public function setPhotoUrl(?string $photoUrl): static
    {
        $this->photoUrl = $photoUrl;

        return $this;
    }

    /**
     * @return Collection<int, DevisStation>
     */
    public function getDevisStationsGasoil(): Collection
    {
        return $this->devisStationsGasoil;
    }

    public function addDevisStationGasoil(DevisStationGasoil $devisStationGasoil): static
    {
        if (!$this->devisStationsGasoil->contains($devisStationGasoil)) {
            $this->devisStationsGasoil->add($devisStationGasoil);
            $devisStationGasoil->setUtilisateur($this);
        }

        return $this;
    }

    public function removeDevisStationGasoil(DevisStationGasoil $devisStationGasoil): static
    {
        if ($this->devisStationsGasoil->removeElement($devisStationGasoil)) {
            // set the owning side to null (unless already changed)
            if ($devisStationGasoil->getUtilisateur() === $this) {
                $devisStationGasoil->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Bons>
     */
    public function getBons(): Collection
    {
        return $this->bons;
    }

    public function addBon(Bons $bon): static
    {
        if (!$this->bons->contains($bon)) {
            $this->bons->add($bon);
            $bon->setUtilisateur($this);
        }

        return $this;
    }

    public function removeBon(Bons $bon): static
    {
        if ($this->bons->removeElement($bon)) {
            // set the owning side to null (unless already changed)
            if ($bon->getUtilisateur() === $this) {
                $bon->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BudgetSommeTotal>
     */
    public function getBudgetSommeTotals(): Collection
    {
        return $this->budgetSommeTotals;
    }

    public function addBudgetSommeTotal(BudgetSommeTotal $budgetSommeTotal): static
    {
        if (!$this->budgetSommeTotals->contains($budgetSommeTotal)) {
            $this->budgetSommeTotals->add($budgetSommeTotal);
            $budgetSommeTotal->setUtilisateur($this);
        }

        return $this;
    }

    public function removeBudgetSommeTotal(BudgetSommeTotal $budgetSommeTotal): static
    {
        if ($this->budgetSommeTotals->removeElement($budgetSommeTotal)) {
            // set the owning side to null (unless already changed)
            if ($budgetSommeTotal->getUtilisateur() === $this) {
                $budgetSommeTotal->setUtilisateur(null);
            }
        }

        return $this;
    }


}
