<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use App\Service\ServicePhoto;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Cast\String_;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class UtilisateurController extends AbstractController
{

    private EntityManagerInterface $entityManager;
    private ServicePhoto $photoService;
    private $params;
    private UtilisateurRepository $utilisateurRepository;


    public function __construct(EntityManagerInterface $entityManager, ServicePhoto $photoService, UtilisateurRepository $utilisateurRepository)
    {
        $this->entityManager = $entityManager;
        $this->photoService = $photoService;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    /**
     * @throws RandomException
     */
    #[Route('/utilisateur/ajouter', name: 'user_register', methods: ['POST'])]
    // AJOUTER UN UTILISATEUR
    public function ajouterUtilisateur(Request $request): JsonResponse
    {
        $userData = json_decode($request->getContent(), true);
        $nomUtilisateur = $userData['nomUtilisateur'];
        $prenomUtilisateur = $userData['prenomUtilisateur'];
        $emailUtilisateur = $this->generateEmail($nomUtilisateur, $prenomUtilisateur);
        $motDePasse = $this->generateMotDePasse();

        $utilisateur = new Utilisateur();
        $utilisateur->setNomUtilisateur($nomUtilisateur);
        $utilisateur->setPrenomUtilisateur($prenomUtilisateur);
        $utilisateur->setEmailUtilisateur($emailUtilisateur);
        $utilisateur->setMotDePasse($motDePasse);

        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'message' => "La creation de votre compte de $prenomUtilisateur $nomUtilisateur est fait avec succès voici son email : $emailUtilisateur et son mot de passe : $motDePasse qui est generer automatiquement!"
            ], 201
        );
    }
    // GENERATE EMAIL AUTO
    private function generateEmail(string $nomUtilisateur, string $prenomUtilisateur): string
    {
        $randomNumber = random_int(01, 99);
        return strtolower(
            substr(
                $nomUtilisateur,0, 1
            ) . '.' . $prenomUtilisateur . $randomNumber . '@gmail.com'
        );
    }
    // GENERATE MOT DE PASSE AUTO
    private function generateMotDePasse(): string
    {
        $length = 8;
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        $motDePasse = '';

        for ($i = 0; $i < $length; $i++) {
            $index = random_int(0, $charactersLength - 1);
            $motDePasse .= $characters[$index];
        }

        return $motDePasse;

    }
    // MODIFIER UN UTILISATEUR
    #[Route('/utilisateur/modifier/{id}', name: 'user_update', methods: ['PUT'])]
    public function modifierUtilisateur(int $id, Request $request): JsonResponse
    {
        $userData = json_decode($request->getContent(), true);
        $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($id);

        if (!$utilisateur) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé'], 404);
        }

        if (isset($userData['nomUtilisateur'])) {
            $utilisateur->setNomUtilisateur($userData['nomUtilisateur']);
        }
        if (isset($userData['prenomUtilisateur'])) {
            $utilisateur->setPrenomUtilisateur($userData['prenomUtilisateur']);
        }
        if (isset($userData['emailUtilisateur'])) {
            $utilisateur->setEmailUtilisateur($userData['emailUtilisateur']);
        }
        if (isset($userData['motDePasse'])) {
            $utilisateur->setMotDePasse($userData['motDePasse']);
        }

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Utilisateur modifié avec succès'], 200);
    }
    // LISTES DES UTILISATEURS
    #[Route('/utilisateur/list', name: 'user_list', methods: ['GET'])]
    public function listerUtilisateurs(): JsonResponse
    {
        $utilisateurs = $this->entityManager->getRepository(Utilisateur::class)->findAll();
        $data = [];

        foreach ($utilisateurs as $utilisateur) {
            $data[] = [
                'id' => $utilisateur->getId(),
                'nomUtilisateur' => $utilisateur->getNomUtilisateur(),
                'prenomUtilisateur' => $utilisateur->getPrenomUtilisateur(),
                'emailUtilisateur' => $utilisateur->getEmailUtilisateur(),
            ];
        }

        return new JsonResponse($data, 200);
    }
    // SUPPRESSION UTILISATEUR
    #[Route('/utilisateur/supprimer/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function supprimerUtilisateur(int $id): JsonResponse
    {
        $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($id);

        if (!$utilisateur) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé'], 404);
        }

        $this->entityManager->remove($utilisateur);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Utilisateur supprimé avec succès'], 200);
    }
    // DETAIL DE L'UTILISATEUR
    #[Route('/utilisateur/detail/{id}', name: 'user_detail', methods: ['GET'])]
    public function detailsUtilisateur(int $id): JsonResponse
    {
        $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($id);

        if (!$utilisateur) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé'], 404);
        }

        $data = [
            'id' => $utilisateur->getId(),
            'nomUtilisateur' => $utilisateur->getNomUtilisateur(),
            'prenomUtilisateur' => $utilisateur->getPrenomUtilisateur(),
            'emailUtilisateur' => $utilisateur->getEmailUtilisateur(),
        ];

        return new JsonResponse($data, 200);
    }
    // CONNEXION D'UN UTILISATEUR
    #[Route('/utilisateur/connexion', name: 'user_login', methods: ['POST'])]
    public function connexionUtilisateur(Request $request): JsonResponse
    {
        $userData = json_decode($request->getContent(), true);
        //$this->logger->info('Données de la requête : ' . print_r($userData, true));

        if (!isset($userData['emailUtilisateur']) || !isset($userData['motDePasse'])) {
            return new JsonResponse(['message' => 'Paramètres manquants'], 400);
        }

        $emailUtilisateur = $userData['emailUtilisateur'];
        $motDePasse = $userData['motDePasse'];

        $utilisateur = $this->utilisateurRepository->findByEmailUtilisateurAndMotDePasse($emailUtilisateur, $motDePasse);

        if (!$utilisateur) {
            return new JsonResponse(['message' => 'Email ou mot de passe incorrect'], 401);
        }

        // Generate a token or session here, depending on your authentication strategy
        return new JsonResponse(['message' => 'Connexion réussie', 'utilisateur' => [
            'id' => $utilisateur->getId(),
            'nomUtilisateur' => $utilisateur->getNomUtilisateur(),
            'prenomUtilisateur' => $utilisateur->getPrenomUtilisateur(),
            'emailUtilisateur' => $utilisateur->getEmailUtilisateur(),
            'motDePasse' => $utilisateur->getMotDePasse()
        ]], 200);
    }
    // AJOUTER PHOTO DE PROFIL
    #[Route('/utilisateur/{id}/ajouter-profil', name: 'user_add_photo', methods: ['POST'])]
    public function ajouterPhoto(int $id, Request $request): JsonResponse
    {
        $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($id);
        if (!$utilisateur) {
            return new JsonResponse(['error' => 'Utilisateur non trouver'], 404);
        }

        $imageFile = $request->files->get('photo');
        // vérification de contenu de la requête
        $allFiles = $request->files->all();

            if ($imageFile) {
                $photoUrl = $this->photoService->ajouterPhoto($imageFile);
                $utilisateur->setPhotoUrl($photoUrl);
                $this->entityManager->flush();

            return new JsonResponse(['message' => 'Photo ajoutée avec succès', 'photoUrl' => $photoUrl], 200);
        }

        return new JsonResponse(['error' => 'Le fichier photo ajouter est requis', 'received_files' => $allFiles], 400);
    }
    // MODIFIER PHOTO DE PROFIL
    #[Route('/utilisateur/{id}/modifier-profil', name: 'user_modify_photo', methods: ['POST'])]
    public function modifierPhoto(int $id, Request $request): JsonResponse
    {
        $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($id);
        if (!$utilisateur) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], 404);
        }

        $imageFileModif = $request->files->get('photo');
        $allFiles = $request->files->all();

        error_log('Files received: ' . print_r($allFiles, true));

        if ($imageFileModif) {
            $photoUrl = $this->photoService->modifierPhoto($imageFileModif);
            $utilisateur->setPhotoUrl($photoUrl);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Photo modifiée avec succès', 'nouvellePhotoUrl' => $photoUrl], 200);
        } else {
            return new JsonResponse(['error' => 'Le fichier photo à modifier est requis', 'nouvellePhotoUrl' => $allFiles], 400);
        }
    }

    // SUPPRIMER PHOTO DE PROFIL
    #[Route('/utilisateur/{id}/supprimer-profil', name: 'user_delete_photo', methods: ['DELETE'])]
    public function supprimerPhoto(int $id): JsonResponse
    {
        $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($id);
        if (!$utilisateur) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], 404);
        }

        $photoUrl = $utilisateur->getPhotoUrl();
        if ($photoUrl) {
            try {
                $this->photoService->supprimerPhoto($photoUrl);
                $utilisateur->setPhotoUrl(null);
                $this->entityManager->flush();

                return new JsonResponse(['message' => 'Photo supprimée avec succès'], 200);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Erreur lors de la suppression de la photo : ' . $e->getMessage()], 500);
            }
        }

        return new JsonResponse(['error' => 'Aucune photo de profil à supprimer'], 400);
    }



}



// Ajout un simple User
/*
 *
    public function register(Request $request): JsonResponse
    {
        $userData = json_decode($request->getContent(), true);
        $emailUtilisateur = $userData['emailUtilisateur'];

        // Vérifier si l'utilisateur existe déjà
        $existingUtilisateur = $this->entityManager->getRepository(Utilisateur::class)->findOneByEmailUtilisateur($emailUtilisateur);
        if ($existingUtilisateur !== null) {
            return new JsonResponse(['message' => 'Un utilisateur avec cet email existe déjà'], 400);
        }

        $utilisateur = new Utilisateur();
        $utilisateur->setNomUtilisateur($userData['nomUtilisateur']);
        $utilisateur->setPrenomUtilisateur($userData['prenomUtilisateur']);
        $utilisateur->setMotDePasse($userData['motDePasse']);
        $utilisateur->setEmailUtilisateur($emailUtilisateur);

        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Utilisateur créé avec succès'], 201);
    }
 */