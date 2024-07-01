<?php

namespace App\Entity;

use App\Repository\DevisStationGasoilRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DevisStationGasoilRepository::class)]
class DevisStationGasoil
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idDevis = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?String $valeurArriver = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?String $valeurDeDepart = null;

    #[ORM\Column(type: Types::STRING)]
    private ?String $consommation = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?String $prixUnite = null;

    #[ORM\Column(type: Types::STRING)]
    private ?String $budgetObtenu = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateAddDevis = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'devisStationsGasoil')]
    private ?Utilisateur $utilisateur = null;

    public function getId(): ?int
    {
        return $this->idDevis;
    }

    public function getValeurArriver(): ?float
    {
        return $this->valeurArriver;
    }

    public function setValeurArriver(float $valeurArriver): static
    {
        $this->valeurArriver = $valeurArriver;

        return $this;
    }

    public function getValeurDeDepart(): ?float
    {
        return $this->valeurDeDepart;
    }

    public function setValeurDeDepart(float $valeurDeDepart): static
    {
        $this->valeurDeDepart = $valeurDeDepart;

        return $this;
    }

    public function getConsommation(): ?float
    {
        return $this->consommation;
    }

    public function setConsommation(float $consommation): static
    {
        $this->consommation = $consommation;

        return $this;
    }

    public function getPrixUnite(): ?float
    {
        return $this->prixUnite;
    }

    public function setPrixUnite(float $prixUnite): static
    {
        $this->prixUnite = $prixUnite;

        return $this;
    }

    public function getBudgetObtenu(): ?float
    {
        return $this->budgetObtenu;
    }

    public function setBudgetObtenu(float $budgetObtenu): static
    {
        $this->budgetObtenu = $budgetObtenu;

        return $this;
    }

    public function getDateAddDevis(): ?\DateTimeInterface
    {
        return $this->dateAddDevis;
    }

    public function setDateAddDevis(\DateTimeInterface $dateAddDevis): static
    {
        $this->dateAddDevis = $dateAddDevis;

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
}
