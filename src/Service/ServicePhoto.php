<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ServicePhoto
{
    private ParameterBagInterface $params;
    private Filesystem $filesystem;

    public function __construct(ParameterBagInterface $params )
    {
        $this->params = $params;
        $this->filesystem = new Filesystem();
    }

    // AJOUTER UN PHOTO DE PROFIL
    public function ajouterPhoto(UploadedFile $imageFile): string
    {
        $imageLocation = $this->params->get('image_directory');

        try {
            $imageRootLocation = $imageLocation;
            if (!$this->filesystem->exists($imageRootLocation)) {
                $this->filesystem->mkdir($imageRootLocation, 0777);
            }

            $imageName = uniqid() . '_' . $imageFile->getClientOriginalName();
            $imageFile->move($imageRootLocation, $imageName);

            return '/images/' . $imageName;
        } catch (IOExceptionInterface $exception) {
            throw new \Exception("Erreur lors du traitement du fichier image : " . $exception->getMessage());
        }
    }
    // MODIFIER UN PHOTO DE PROFIL
    public function modifierPhoto(UploadedFile $imageFileModif): string
    {
        $imageLocation = $this->params->get('image_directory_mod');

        try {
            $imageRootLocation = $imageLocation;
            if (!$this->filesystem->exists($imageRootLocation)) {
                $this->filesystem->mkdir($imageRootLocation, 0777);
            }

            $imageName = uniqid() . '_' . $imageFileModif->getClientOriginalName();
            $imageFileModif->move($imageRootLocation, $imageName);

            return '/images/' . $imageName;
        } catch (IOExceptionInterface $exception) {
            throw new \Exception("Erreur lors du traitement du fichier image : " . $exception->getMessage());
        }
    }
    // SUPPRIMER PHOTO DE PROFIL
    public function supprimerPhoto(string $imagePath): void
    {
        $imageLocation = $this->params->get('image_directory');
        $imageRootLocation = $imageLocation . '/' . basename($imagePath);

        try {
            if ($this->filesystem->exists($imageRootLocation)) {
                $this->filesystem->remove($imageRootLocation);
            } else {
                throw new \Exception('Le fichier image spécifié n\'existe pas.');
            }
        } catch (IOExceptionInterface $exception) {
            throw new \Exception("Erreur lors de la suppression du fichier image : " . $exception->getMessage());
        }
    }

}