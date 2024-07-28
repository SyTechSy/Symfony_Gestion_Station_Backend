<?php

namespace App\Controller;

use App\Entity\Bons;
use App\Entity\Utilisateur;
use App\Repository\BonsRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BonsController extends AbstractController
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

    // AJOUTER UN BON
    #[Route('/add/bon', name: 'ajout_bon', methods: ['POST'])]
    public function ajouterBon(
        Request $request,
        UtilisateurRepository $utilisateurRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ) : JsonResponse
    {
        $dataBon = json_decode($request->getContent(), true);

        $nomDestinataire = $dataBon["nomDestinataire"] ?? '';
        $prixDemander = $dataBon["prixDemander"] ?? '';
        $motif = $dataBon["motif"] ?? '';
        $idUser = $dataBon['utilisateur']['id'];

        $utilisateur = $utilisateurRepository->find($idUser);
        if (!$utilisateur) {
            return $this->json(['error' => 'Utilisateur non trouver'], Response::HTTP_NOT_FOUND);
        }

        $bons = new Bons();
        $bons->setNomDestinataire($nomDestinataire);
        $bons->setPrixDemander($prixDemander);
        $bons->setMotif($motif);
        $bons->setDateAddBon(new \DateTime());
        $bons->setUtilisateur($utilisateur);

        // Validate the entity
        $errors = $validator->validate($bons);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($bons);
        $this->entityManager->flush();

        return new JsonResponse([
            'idBon' => $bons->getId(),
            'nomDestinataire' => $bons->getNomDestinataire(),
            'prixDemander' => $bons->getPrixDemander(),
            'motif' => $bons->getMotif(),
            'dateAddBon' => $bons->getDateAddBon(),
            'utilisateur' => [
                'id' => $utilisateur->getId(),
                'nomUtilisateur' => $utilisateur->getNomUtilisateur(),
                'prenomUtilisateur' => $utilisateur->getPrenomUtilisateur(),
                'emailUtilisateur' => $utilisateur->getEmailUtilisateur(),
                'motDePasse' => $utilisateur->getMotDePasse(),
                'photoUrl' => $utilisateur->getPhotoUrl(),
            ]
        ], Response::HTTP_CREATED);
    }

    // LISTS DE TOUT LES BONS
    #[Route('/list/bons/{id}', name: 'list_bons', methods: ['GET'])]
    public function listBons(int $id): JsonResponse
    {
        // Vérifier si l'utilisateur existe
        $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($id);
        if (!$utilisateur) {
            throw new NotFoundHttpException("L'utilisateur avec l'ID $id n'existe pas.");
        }

        // Récupérer les devis associés à l'utilisateur
        $bons = $this->entityManager->getRepository(Bons::class)->findBy(['utilisateur' => $id]);

        // Si l'utilisateur n'a pas créé de devis
        if (empty($bons)) {
            return new JsonResponse(['message' => "L'utilisateur avec l'ID $id n'a pas créé de bon."], 404);
        }

        // Construire la réponse avec les bons trouvés
        $dataBon = [];
        /*$formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::SHORT,
            null,
            null,
            'EEEE le dd/MM/yyyy - HH:mm'
        );*/

        foreach ($bons as $bon) {
            //$formattedDate = $formatter->format($bon->getDateAddBon());
                array_unshift($dataBon, [
                'idBon' => $bon->getId(),
                'nomDestinataire' => $bon->getNomDestinataire(),
                'prixDemander' => $bon->getPrixDemander(),
                'motif' => $bon->getMotif(),
                'dateAddBon' => $bon->getDateAddBon(),
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

    //SUPPRESSION UN DEVIS Essense
    #[Route('/delete/bon/{id}', name: 'bon_delete', methods: ['DELETE'])]
    public function deleteDevisGasoil(int $id): JsonResponse
    {
        $devisStation = $this->entityManager->getRepository(Bons::class)->find($id);

        if (!$devisStation) {
            return new JsonResponse(['message' => 'Bon non trouvé'], 404);
        }

        $this->entityManager->remove($devisStation);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Bon supprimé avec succès'], 200);
    }

    // MODIFICATION BONS
    #[Route('/edit/bon/{id}', name: 'bons_update', methods: ['PUT'])]
    public function updateBon(
        int $id, Request $request,
        BonsRepository $bonsRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse
    {
        $dataBon = json_decode($request->getContent(), true);
        error_log(print_r($dataBon, true));

        // Trouve le devis gasoil par ID
        $bonStation = $bonsRepository->find($id);
        if (!$bonStation) {
            return $this->json(['error' => 'Bon non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $nomDestinataire = $dataBon['nomDestinataire'];
        $prixDemander = $dataBon['prixDemander'];
        $motif = $dataBon['motif'];

        // Met à jour les champs du Bon
        $bonStation->setNomDestinataire($nomDestinataire);
        $bonStation->setPrixDemander($prixDemander);
        $bonStation->setMotif($motif);
        $bonStation->setDateAddBon(new \DateTime());

        $entityManager->persist($bonStation);
        $entityManager->flush();

        return new JsonResponse([
            'idBon' => $bonStation->getId(),
            'nomDestinataire' => $bonStation->getNomDestinataire(),
            'prixDemander' => $bonStation->getPrixDemander(),
            'motid' => $bonStation->getMotif(),
            'dateAddBon' => $bonStation->getDateAddBon()->format('Y-m-d H:i:s'),
            'utilisateur' => [
                'id' => $bonStation->getUtilisateur()->getId(),
                'nomUtilisateur' => $bonStation->getUtilisateur()->getNomUtilisateur(),
                'prenomUtilisateur' => $bonStation->getUtilisateur()->getPrenomUtilisateur(),
                'emailUtilisateur' => $bonStation->getUtilisateur()->getEmailUtilisateur(),
                'motDePasse' => $bonStation->getUtilisateur()->getMotDePasse(),
                'photoUrl' => $bonStation->getUtilisateur()->getPhotoUrl(),
            ]
        ], Response::HTTP_OK);
    }
}
