<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Repository\AdminRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{

    private $entityManager;
    private $adminRepository;
    //CONSTRUCTION POUR MON INJECTION DE DEPENDANCE
    public function __construct(EntityManagerInterface $entityManager, AdminRepository $adminRepository) {
        $this->entityManager = $entityManager;
        $this->adminRepository = $adminRepository;
    }
    // AJOUTER UN ADMINISTRATEUR
    #[Route('/add/admin', name: 'add_admin', methods: ['POST'])]
    public function addAdmin(Request $request): JsonResponse
    {
        $adminData = json_decode($request->getContent(), true);
        $emailAdmin = $adminData['emailAdmin'];
        $nomAdmin = $adminData['nomAdmin'];
        $prenomAdmin = $adminData['prenomAdmin'];
        $motDePasse = $adminData['motDePasse'];
        $verifAdmin = $this->entityManager->getRepository(Admin::class)->findByEmailAdmin($emailAdmin);
        if ($verifAdmin !== null) {
            return new JsonResponse(["message" => "Désole $nomAdmin Admin avec cet email existe déjà"]);
        }
        $admin = new Admin();
        $admin->setEmailAdmin($emailAdmin);
        $admin->setNomAdmin($nomAdmin);
        $admin->setPrenomAdmin($prenomAdmin);
        $admin->setMotDePasse($motDePasse);
        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        return new JsonResponse(
            [
                'Message' => "Administrateur $prenomAdmin $nomAdmin est crée avec succès"
            ], 201
        );
    }
    // CONNEXION ADMINISTRATEUR
    /*#[Route('/login/admin', name: 'admin_login', methods: ['POST'])]
    public function loginAdmin(Request $request): JsonResponse
    {
        $adminData = json_decode($request->getContent(), true);

        if (!isset($adminData['emailAdmin']) || !isset($adminData['motDePasse'])) {
            return new JsonResponse(['message' => 'Paramètres manquants'], 400);
        }

        $emailAdmin = $adminData['emailAdmin'];
        $motDePasse = $adminData['motDePasse'];

        $admin = $this->adminRepository->findByEmailAdminAndMotDePasse($emailAdmin, $motDePasse);

        if (!$admin) {
            return new JsonResponse(['message' => 'Email ou mot de passe incorrect'], 401);
        }

        // Retournez les données de l'administrateur dans la réponse
        return new JsonResponse([
            'message' => 'Connexion réussie',
            'admin' => [
                'id' => $admin->getId(),
                'nomAdmin' => $admin->getNomAdmin(),
                'prenomAdmin' => $admin->getPrenomAdmin(),
                'emailAdmin' => $admin->getEmailAdmin(),
                'motDePasse' => $admin->getMotDePasse()
            ]
        ], 200);
    }*/
    #[Route('/login/admin', name: 'admin_login', methods: ['POST'])]
    public function loginAdmin(Request $request, MailerInterface $mailer): JsonResponse
    {
        $adminData = json_decode($request->getContent(), true);

        if (!isset($adminData['emailAdmin']) || !isset($adminData['motDePasse'])) {
            return new JsonResponse(['message' => 'Paramètres manquants'], 400);
        }

        $emailAdmin = $adminData['emailAdmin'];
        $motDePasse = $adminData['motDePasse'];

        $admin = $this->adminRepository->findByEmailAdminAndMotDePasse($emailAdmin, $motDePasse);

        if (!$admin) {
            return new JsonResponse(['message' => 'Email ou mot de passe incorrect'], 401);
        }

        // Générer le code de vérification
        $verificationCode = $this->generateVerificationCode();
        $expirationTime = new \DateTime('+10 minutes');

        // Envoyer le code par email
        $this->sendVerificationEmail($emailAdmin, $verificationCode, $mailer);

        // Stocker le code et son expiration dans la base de données
        $admin->setVerificationCode($verificationCode);
        $admin->setVerificationCodeExpiration($expirationTime);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Code de vérification envoyé. Veuillez vérifier votre email.',
            'admin' => [
                'id' => $admin->getId(),
                'nomAdmin' => $admin->getNomAdmin(),
                'prenomAdmin' => $admin->getPrenomAdmin(),
                'emailAdmin' => $admin->getEmailAdmin(),
                // Ne pas inclure le mot de passe dans la réponse
            ]
        ], 200);
    }

    #[Route('/verify/admin', name: 'admin_verify', methods: ['POST'])]
    public function verifyAdmin(Request $request): JsonResponse
    {
        $verificationData = json_decode($request->getContent(), true);

        if (!isset($verificationData['emailAdmin']) || !isset($verificationData['code'])) {
            return new JsonResponse(['message' => 'Paramètres manquants'], 400);
        }

        $emailAdmin = $verificationData['emailAdmin'];
        $code = $verificationData['code'];

        // Récupérer l'admin par email
        $admin = $this->adminRepository->findOneBy(['emailAdmin' => $emailAdmin]);

        if (!$admin) {
            return new JsonResponse(['message' => 'Admin non trouvé'], 404);
        }

        if ($admin->getVerificationCodeExpiration() < new \DateTime()) {
            return new JsonResponse(['message' => 'Code de vérification expiré'], 401);
        }

        if ($admin->getVerificationCode() === $code) {
            // Code correct, connexion réussie
            return new JsonResponse(['message' => 'Vérification réussie'], 200);
        } else {
            // Code incorrect
            return new JsonResponse(['message' => 'Code de vérification incorrect'], 401);
        }
    }

    private function generateVerificationCode() {
        return rand(10000, 99999); // Génère un code aléatoire à 5 chiffres
    }

    public function sendVerificationEmail($email, $code, MailerInterface $mailer) {
        $emailMessage = (new Email())
            ->from('sydiakaridia38@gmail.com')
            ->to($email)
            ->subject('Votre code de vérification')
            ->text("Votre code de vérification est : $code");

        $mailer->send($emailMessage);
    }

    // MODIFICATION DE L'ADMINISTRATEUR
    #[Route('/edit/admin/{idAdmin}', name: 'edit_admin', methods: ['PUT'])]
    public function editAdmin(Request $request, int $idAdmin): JsonResponse
    {
        $adminData = json_decode($request->getContent(), true);
        $Admin = $this->entityManager->getRepository(Admin::class)->find($idAdmin);
        // Une verification si admin existe pas
        if (!$Admin) {
            return new JsonResponse(["message" => "Administrateur n'existe pas"], 404);
        }
        // Les champs a modifier
        if (isset($adminData['nomAdmin'])) {
            $Admin->setNomAdmin($adminData['nomAdmin']);
        }
        if (isset($adminData['prenomAdmin'])) {
            $Admin->setPrenomAdmin($adminData['prenomAdmin']);
        }
        $this->entityManager->flush();
        //return new JsonResponse(['message' => 'L\'admin est modifier avec succès']);
        return new JsonResponse([
            'message' => 'L\'admin est modifier avec succès',
            $adminData
        ]);
    }
    // SUPPRESSION DES ADMINISTRATEUR
    #[Route('/delete/admin/{idAdmin}', name: 'edit_admin', methods: ['DELETE'])]
    public function deleteAdmin(Request $request ,int $idAdmin): JsonResponse
    {
        $Admin = $this->entityManager->getRepository(Admin::class)->find($idAdmin);

        if (!$Admin) {
            return new JsonResponse(["message" => "Administrateur n'existe pas"], 404);
        }

        $this->entityManager->remove($Admin);
        $this->entityManager->flush();
        return new JsonResponse(['message' => 'L\'administrateur est supprimer avec succes']);
    }
    // LISTES DES ADMINISTRATEUR
    #[Route('/list/admins', name: 'list_admin', methods: ['GET'])]
    public function listAdmin(): JsonResponse {
        $admins = $this->entityManager->getRepository(Admin::class)->findAll();
        $adminData = [];

        // creation d'une fonction foreach
        foreach ($admins as $admin) {
            $adminData[] = [
                'idAdmin' => $admin->getId(),
                'nomAdmin' => $admin->getNomAdmin(),
                'prenomAdmin' => $admin->getPrenomAdmin(),
                'emailAdmin' => $admin->getEmailAdmin(),
                'motDePasse' => $admin->getMotDePasse()
            ];
        }
        return new JsonResponse($adminData, 200);
    }
    // DETAIL DES ADMINISTATEUR
    #[Route('/profil/admin/{idAdmin}', name: 'profil_admin', methods: ['GET'])]
    public function detailAdmin(int $idAdmin): JsonResponse {
        $admin = $this->entityManager->getRepository(Admin::class)->find($idAdmin);
        if (!$admin) {
            return new JsonResponse(["message" => "Administrateur n'existe pas"], 404);
        }

        $adminData = [
            'idAdmin' => $admin->getId(),
            'nomAdmin' => $admin->getNomAdmin(),
            'prenomAdmin' => $admin->getPrenomAdmin(),
            'emailAdmin' => $admin->getEmailAdmin(),
            'motDePasse' => $admin->getMotDePasse()
        ];
        return new JsonResponse($adminData, 200);
    }
    // AJOUTER PHOTO DE PROFIL
    // MODIFIER PHOTO DE PROFIL
    // SUPPRIMER PHOTO DE PROFIL
}


/*
 #[Route('/admin/ajouter', name: 'admin ajouter', methods: ['POST'])]
    public function ajouterAdmin(Request $request) : JsonResponse
    {
        $adminData = json_decode($request->getContent(), true);
        $emailAdmin = $adminData['emailAdmin'];
        // Verification si Admin exist ou pas
        $verifAdmin = $this->entityManager->getRepository(Admin::class)->findOneByEmailAdmin($emailAdmin);
        if ($verifAdmin !== null) {
            return new JsonResponse(["message" => "Admin avec cet email existe déjà"]);
        }

        $admin = new Admin();
        $admin->setNomAdmin($adminData["nomAdmin"]);
        $admin->setPrenomAdmin($adminData["prenomAdmin"]);
        $admin->setEmailAdmin($adminData["emailAdmin"]);
        $admin->setMotDePasse($adminData["motDePasse"]);
        $admin->setVerificationCode($adminData["verificationCode"]);

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        return new JsonResponse($admin);
    }
 */