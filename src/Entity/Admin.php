<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdminRepository::class)]
class Admin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idAdmin = null;

    #[ORM\Column(length: 255)]
    private ?string $nomAdmin = null;

    #[ORM\Column(length: 255)]
    private ?string $prenomAdmin = null;

    #[ORM\Column(length: 255)]
    private ?string $emailAdmin = null;

    #[ORM\Column(length: 255)]
    private ?string $motDePasse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $verificationCode = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $verificationCodeExpiration = null;

    public function getId(): ?int
    {
        return $this->idAdmin;
    }

    public function getNomAdmin(): ?string
    {
        return $this->nomAdmin;
    }

    public function setNomAdmin(string $nomAdmin): static
    {
        $this->nomAdmin = $nomAdmin;

        return $this;
    }

    public function getPrenomAdmin(): ?string
    {
        return $this->prenomAdmin;
    }

    public function setPrenomAdmin(string $prenomAdmin): static
    {
        $this->prenomAdmin = $prenomAdmin;

        return $this;
    }

    public function getEmailAdmin(): ?string
    {
        return $this->emailAdmin;
    }

    public function setEmailAdmin(string $emailAdmin): static
    {
        $this->emailAdmin = $emailAdmin;

        return $this;
    }

    public function getMotDePasse(): ?string
    {
        return $this->motDePasse;
    }

    public function setMotDePasse(string $motDePasse): static
    {
        $this->motDePasse = $motDePasse;

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

    public function getVerificationCode(): ?string
    {
        return $this->verificationCode;
    }

    public function setVerificationCode(string $verificationCode): static
    {
        $this->verificationCode = $verificationCode;

        return $this;
    }

    public function getVerificationCodeExpiration(): ?\DateTimeInterface
    {
        return $this->verificationCodeExpiration;
    }

    public function setVerificationCodeExpiration(?\DateTimeInterface $verificationCodeExpiration): static
    {
        $this->verificationCodeExpiration = $verificationCodeExpiration;

        return $this;
    }
}
