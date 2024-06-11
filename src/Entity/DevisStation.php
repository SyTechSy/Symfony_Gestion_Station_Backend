<?php

namespace App\Entity;

use App\Repository\DevisStationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DevisStationRepository::class)]
class DevisStation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?string $valeurArriver = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?string $valeurDeDepart = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?string $consommation = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?string $prixUnite = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?string $budgetObtenu = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateAddDevis = null;

    #[ORM\ManyToOne(inversedBy: 'utilisateur')]
    private ?Utilisateur $devisStation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValeurArriver(): ?string
    {
        return $this->valeurArriver;
    }

    public function setValeurArriver(string $valeurArriver): static
    {
        $this->valeurArriver = $valeurArriver;

        return $this;
    }

    public function getValeurDeDepart(): ?string
    {
        return $this->valeurDeDepart;
    }

    public function setValeurDeDepart(string $valeurDeDepart): static
    {
        $this->valeurDeDepart = $valeurDeDepart;

        return $this;
    }

    public function getConsommation(): ?string
    {
        return $this->consommation;
    }

    public function setConsommation(string $consommation): static
    {
        $this->consommation = $consommation;

        return $this;
    }

    public function getPrixUnite(): ?string
    {
        return $this->prixUnite;
    }

    public function setPrixUnite(string $prixUnite): static
    {
        $this->prixUnite = $prixUnite;

        return $this;
    }

    public function getBudgetObtenu(): ?string
    {
        return $this->budgetObtenu;
    }

    public function setBudgetObtenu(string $budgetObtenu): static
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

    public function getDevisStation(): ?Utilisateur
    {
        return $this->devisStation;
    }

    public function setDevisStation(?Utilisateur $devisStation): static
    {
        $this->devisStation = $devisStation;

        return $this;
    }

}
