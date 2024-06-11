<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomUtilisateur = null;

    #[ORM\Column(length: 255)]
    private ?string $prenomUtilisateur = null;

    #[ORM\Column(length: 180)]
    private ?string $emailUtilisateur = null;

    #[ORM\Column(length: 14)]
    private ?string $motDePasse = null;


    /**
     * @var Collection<int, DevisStation>
     */
    #[ORM\OneToMany(targetEntity: DevisStation::class, mappedBy: 'devisStation')]
    private Collection $devisStations;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoUrl = null;






    public function __construct()
    {
        $this->devisStations = new ArrayCollection();
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
            $devisStation->setDevisStation($this);
        }

        return $this;
    }

    public function removeDevisStation(DevisStation $devisStation): static
    {
        if ($this->devisStations->removeElement($devisStation)) {
            // set the owning side to null (unless already changed)
            if ($devisStation->getDevisStation() === $this) {
                $devisStation->setDevisStation(null);
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



}
