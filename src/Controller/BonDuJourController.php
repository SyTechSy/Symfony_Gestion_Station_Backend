<?php

namespace App\Controller;

use App\Entity\BonDuJour;
use App\Entity\Bons;
use App\Entity\Utilisateur;
use App\Repository\BonDuJourRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BonDuJourController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UtilisateurRepository $utilisateurRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UtilisateurRepository $utilisateurRepository
    ) {
        $this->entityManager = $entityManager;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    #[Route('/add/bon/du/jour', name: 'app_bon_du_jour', methods: ['POST'])]
    public function createBonDuJour(
        Request $request,
        BonDuJourRepository $bonRepository
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $idUser = $data['utilisateur']['id'];
        $today = new \DateTime('today');
        $jourSemaine = $today->format('l'); // Récupère le jour en anglais

        // Traduction en français
        $joursFrancais = [
            'Sunday' => 'Dimanche',
            'Monday' => 'Lundi',
            'Tuesday' => 'Mardi',
            'Wednesday' => 'Mercredi',
            'Thursday' => 'Jeudi',
            'Friday' => 'Vendredi',
            'Saturday' => 'Samedi',
        ];

        $jourSemaineFrancais = $joursFrancais[$jourSemaine];

        // Vérifier si un bon a déjà été créé aujourd'hui pour cet utilisateur
        $existingBon = $bonRepository->findOneBy(['date' => $today, 'utilisateur' => $idUser]);

        if ($existingBon) {
            return new JsonResponse([
                'error' => 'Vous avez déjà créé un bon aujourd\'hui.'
            ], Response::HTTP_CONFLICT);
        }

        $utilisateur = $this->utilisateurRepository->find($idUser);
        if (!$utilisateur) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Créer un nouveau bon
        $bon = new BonDuJour();
        $bon->setDate($today);
        $bon->setUtilisateur($utilisateur);

        $this->entityManager->persist($bon);
        $this->entityManager->flush();

        return new JsonResponse([
            'idBonJour' => $bon->getId(),
            'dateAddBonDuJour' => $jourSemaineFrancais, // Renvoie le jour de la semaine
            'utilisateur' => [
                'id' => $utilisateur->getId(),
                'nomUtilisateur' => $utilisateur->getNomUtilisateur(),
                'prenomUtilisateur' => $utilisateur->getPrenomUtilisateur(),
                'emailUtilisateur' => $utilisateur->getEmailUtilisateur(),
                // Évite d'exposer le mot de passe en production
                'photoUrl' => $utilisateur->getPhotoUrl(),
            ]
        ], Response::HTTP_CREATED);
    }

    // LISTS DE TOUT LES BONS par jour
    #[Route('/list/bon/du/jour/{id}', name: 'list_bons_du_jours', methods: ['GET'])]
    public function listBonDuJour(int $id): JsonResponse
    {
        // Vérifier si l'utilisateur existe
        $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($id);
        if (!$utilisateur) {
            throw new NotFoundHttpException("L'utilisateur avec l'ID $id n'existe pas.");
        }

        // Récupérer les bons du jour associés à l'utilisateur
        $bonsdujours = $this->entityManager->getRepository(BonDuJour::class)->findBy(['utilisateur' => $id]);

        // Si l'utilisateur n'a pas créé de bon du jour
        if (empty($bonsdujours)) {
            return new JsonResponse(['message' => "L'utilisateur avec l'ID $id n'a pas créé de bon du jour."], 404);
        }

        // Initialiser le tableau des bons
        $dataBon = [];

        // Formatter pour afficher uniquement le jour de la semaine
        $formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::NONE,
            null,
            null,
            'EEEE' // Format pour afficher uniquement le jour de la semaine
        );

        // Parcourir les bons du jour et formater la date
        foreach ($bonsdujours as $bonbyday) {
            $formattedDay = $formatter->format($bonbyday->getDate());

            array_unshift($dataBon, [
                'idBonJour' => $bonbyday->getId(),
                'dateAddBonDuJour' => $formattedDay, // Afficher uniquement le jour
                'utilisateur' => [
                    'id' => $utilisateur->getId(),
                    'nomUtilisateur' => $utilisateur->getNomUtilisateur(),
                    'prenomUtilisateur' => $utilisateur->getPrenomUtilisateur(),
                    'emailUtilisateur' => $utilisateur->getEmailUtilisateur(),
                    'motDePasse' => $utilisateur->getMotDePasse(),
                    'photoUrl' => $utilisateur->getPhotoUrl(),
                ]
            ]);
        }

        return new JsonResponse($dataBon, 200);
    }

}
