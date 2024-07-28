<?php

namespace App\Entity;

use App\Repository\BonsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BonsRepository::class)]
class Bons
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idBon = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[Assert\NotBlank(message: "Le champs 'nom destinataire' ne doit pas être vide'")]
    private ?string $nomDestinataire = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le champ 'prix demander' ne doit pas être vide.")]
    private ?string $prixDemander = null;

    #[ORM\ManyToOne(inversedBy: 'bons')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateAddBon = null;

    #[ORM\Column(length: 255)]
    private ?string $motif = null;

    public function getId(): ?int
    {
        return $this->idBon;
    }

    public function getNomDestinataire(): ?string
    {
        return $this->nomDestinataire;
    }

    public function setNomDestinataire(string $nomDestinataire): static
    {
        $this->nomDestinataire = $nomDestinataire;

        return $this;
    }

    public function getPrixDemander(): ?string
    {
        return $this->prixDemander;
    }

    public function setPrixDemander(string $prixDemander): static
    {
        $this->prixDemander = $prixDemander;

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

    public function getDateAddBon(): ?\DateTimeInterface
    {
        return $this->dateAddBon;
    }

    public function setDateAddBon(?\DateTimeInterface $dateAddBon): static
    {
        $this->dateAddBon = $dateAddBon;

        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(string $motif): static
    {
        $this->motif = $motif;

        return $this;
    }
}
