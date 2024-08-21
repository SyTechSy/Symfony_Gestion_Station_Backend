<?php

namespace App\Entity;

use App\Repository\BudgetSommeTotalRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BudgetSommeTotalRepository::class)]
class BudgetSommeTotal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idBudgetTotal = null;

    #[ORM\Column]
    private ?float $budgetTotalEssence = null;

    #[ORM\Column]
    private ?float $budgetTotalGasoil = null;

    #[ORM\Column]
    private ?float $sommeTotalBudgets = null;

    #[ORM\Column]
    private ?float $argentRecuTravail = null;

    #[ORM\Column]
    private ?float $perteArgent = null;

    #[ORM\Column]
    private ?float $gainArgent = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateAddBudgetTotal = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'budgetSommeTotals')]
    private ?Utilisateur $utilisateur = null;


    public function getId(): ?int
    {
        return $this->idBudgetTotal;
    }

    public function getBudgetTotalEssence(): ?float
    {
        return $this->budgetTotalEssence;
    }

    public function setBudgetTotalEssence(float $budgetTotalEssence): static
    {
        $this->budgetTotalEssence = $budgetTotalEssence;

        return $this;
    }

    public function getBudgetTotalGasoil(): ?float
    {
        return $this->budgetTotalGasoil;
    }

    public function setBudgetTotalGasoil(float $budgetTotalGasoil): static
    {
        $this->budgetTotalGasoil = $budgetTotalGasoil;

        return $this;
    }

    public function getSommeTotalBudgets(): ?float
    {
        return $this->sommeTotalBudgets;
    }

    public function setSommeTotalBudgets(float $sommeTotalBudgets): static
    {
        $this->sommeTotalBudgets = $sommeTotalBudgets;

        return $this;
    }

    public function getArgentRecuTravail(): ?float
    {
        return $this->argentRecuTravail;
    }

    public function setArgentRecuTravail(float $argentRecuTravail): static
    {
        $this->argentRecuTravail = $argentRecuTravail;

        return $this;
    }

    public function getPerteArgent(): ?float
    {
        return $this->perteArgent;
    }

    public function setPerteArgent(float $perteArgent): static
    {
        $this->perteArgent = $perteArgent;

        return $this;
    }

    public function getGainArgent(): ?float
    {
        return $this->gainArgent;
    }

    public function setGainArgent(float $gainArgent): static
    {
        $this->gainArgent = $gainArgent;

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

    public function getDateAddBudgetTotal(): ?\DateTimeInterface
    {
        return $this->dateAddBudgetTotal;
    }

    public function setDateAddBudgetTotal(\DateTimeInterface $dateAddBudgetTotal): static
    {
        $this->dateAddBudgetTotal = $dateAddBudgetTotal;

        return $this;
    }
}
