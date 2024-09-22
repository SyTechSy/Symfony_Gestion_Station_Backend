<?php

namespace App\Controller;

use App\Entity\DevisStation;
use App\Entity\Utilisateur;
use App\Repository\DevisStationRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class DevisController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UtilisateurRepository $utilisateurRepository;

    public function __construct(EntityManagerInterface $entityManager, UtilisateurRepository $utilisateurRepository)
    {
        $this->entityManager = $entityManager;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    /**
     * @throws Exception
     */
    //#[Route('/add/devis', name: 'create_devis', methods: ['POST'])]
    #[Route('/add/devis', name: 'create_devis', methods: ['POST'])]
    public function createDevis(
        Request $request,
        EntityManagerInterface $entityManager,
        UtilisateurRepository $utilisateurRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        error_log(print_r($data, true));

        $valeurArriver = $data['valeurArriver'];
        $valeurDeDepart = $data['valeurDeDepart'];
        $prixUnite = $data['prixUnite'];
        $idUser = $data['utilisateur']['id'];

        $utilisateur = $utilisateurRepository->find($idUser);
        if (!$utilisateur) {
            return $this->json(['error' => 'Utilisateur non trouver'], Response::HTTP_NOT_FOUND);
        }
        $consommation = $valeurArriver - $valeurDeDepart;
        $budgetObtenu = $consommation * $prixUnite;
        //$dateAddDevis = new \DateTime();

        //$dateDevis = \DateTime::createFromFormat('Y-m-d H:i:s', $data['Date add devis']['date']);
        //$dateDevis->setTimezone(new \DateTimeZone('Europe/Paris'));

        $devisStation = new DevisStation();
        $devisStation->setValeurArriver($valeurArriver);
        $devisStation->setValeurDeDepart($valeurDeDepart);
        $devisStation->setPrixUnite($prixUnite);
        $devisStation->setConsommation($consommation);
        $devisStation->setBudgetObtenu($budgetObtenu);
        $devisStation->setDateAddDevis(new \DateTime());
        $devisStation->setUtilisateur($utilisateur);

        $entityManager->persist($devisStation);
        $entityManager->flush();

        //return $this->json(['message' => 'Devis créé avec succès', 'id' => $devisStation->getId()], Response::HTTP_CREATED);
        return new JsonResponse([
            'idDevis' => $devisStation->getId(),
            'valeurArriver' => $devisStation->getValeurArriver(),
            'valeurDeDepart' => $devisStation->getValeurDeDepart(),
            'consommation' => $devisStation->getConsommation(),
            'prixUnite' => $devisStation->getPrixUnite(),
            'budgetObtenu' => $devisStation->getBudgetObtenu(),
            'dateAddDevis' => $devisStation->getDateAddDevis()->format('Y-m-d H:i:s'),
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
    #[Route('/list/devis/{id}', name: 'list_devis', methods: ['GET'])]
    public function listDevis(int $id): JsonResponse 
    {
        // Vérifier si l'utilisateur existe
        $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($id);
        if (!$utilisateur) {
            throw new NotFoundHttpException("L'utilisateur avec l'ID $id n'existe pas.");
        }

        // Récupérer les devis associés à l'utilisateur
        $devisStations = $this->entityManager->getRepository(DevisStation::class)->findBy(['utilisateur' => $id]);

        // Si l'utilisateur n'a pas créé de devis
        if (empty($devisStations)) {
            return new JsonResponse(['message' => "L'utilisateur avec l'ID $id n'a pas créé de devis."], 404);
        }

        // Construire la réponse avec les devis trouvés
        $dataDevis = [];
        foreach ($devisStations as $devisStation) {
            array_unshift($dataDevis, [
                'idDevis' => $devisStation->getId(),
                'valeurArriver' => $devisStation->getValeurArriver(),
                'valeurDeDepart' => $devisStation->getValeurDeDepart(),
                'consommation' => $devisStation->getConsommation(),
                'prixUnite' => $devisStation->getPrixUnite(),
                'budgetObtenu' => $devisStation->getBudgetObtenu(),
                'dateAddDevis' => $devisStation->getDateAddDevis(),
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

        return new JsonResponse($dataDevis, 200);
    }

    // SUPPRESSION UN DEVIS Essense
    #[Route('/delete/devis/{id}', name: 'devis_delete', methods: ['DELETE'])]
    public function deleteDevisEssence(int $id): JsonResponse
    {
        $devisStation = $this->entityManager->getRepository(DevisStation::class)->find($id);

        if (!$devisStation) {
            return new JsonResponse(['message' => 'Devis non trouvé'], 404);
        }

        $this->entityManager->remove($devisStation);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Devis supprimé avec succès'], 200);
    }

    // MODIFICATION DE DEVIS ESSENCE
    #[Route('/edit/devisEssence/{id}', name: 'devisEssence_update', methods: ['PUT'])]
    public function updateDevis(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        DevisStationRepository $devisStationRepository): JsonResponse
    {
        $dataEssence = json_decode($request->getContent(), true);
        error_log(print_r($dataEssence, true));

        // Trouve le  essence par ID
        $devisStation = $devisStationRepository->find($id);
        if (!$devisStation) {
            return $this->json(['error' => 'Devis essence non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $valeurArriver = $dataEssence['valeurArriver'];
        $valeurDeDepart = $dataEssence['valeurDeDepart'];
        $prixUnite = $dataEssence['prixUnite'];

        // Recalcule la consommation et le budget
        $consommation = $valeurArriver - $valeurDeDepart;
        $budgetObtenu = $consommation * $prixUnite;

        // Met à jour les champs du devis essence
        $devisStation->setValeurArriver($valeurArriver);
        $devisStation->setValeurDeDepart($valeurDeDepart);
        $devisStation->setPrixUnite($prixUnite);
        $devisStation->setConsommation($consommation);
        $devisStation->setBudgetObtenu($budgetObtenu);
        $devisStation->setDateAddDevis(new \DateTime());

        $entityManager->persist($devisStation);
        $entityManager->flush();

        return new JsonResponse([
            'idDevis' => $devisStation->getId(),
            'valeurArriver' => $devisStation->getValeurArriver(),
            'valeurDeDepart' => $devisStation->getValeurDeDepart(),
            'consommation' => $devisStation->getConsommation(),
            'prixUnite' => $devisStation->getPrixUnite(),
            'budgetObtenu' => $devisStation->getBudgetObtenu(),
            'dateAddDevis' => $devisStation->getDateAddDevis()->format('Y-m-d H:i:s'),
            'utilisateur' => [
                'id' => $devisStation->getUtilisateur()->getId(),
                'nomUtilisateur' => $devisStation->getUtilisateur()->getNomUtilisateur(),
                'prenomUtilisateur' => $devisStation->getUtilisateur()->getPrenomUtilisateur(),
                'emailUtilisateur' => $devisStation->getUtilisateur()->getEmailUtilisateur(),
                'motDePasse' => $devisStation->getUtilisateur()->getMotDePasse(),
                'photoUrl' => $devisStation->getUtilisateur()->getPhotoUrl(),
            ]
        ], Response::HTTP_OK);
    }

}


/*#[Route('/list/devis', name: 'list_devis', methods: ['GET'])]
    public  function listDevis(): JsonResponse
    {
        $devisStations = $this->entityManager->getRepository(DevisStation::class)->findAll();
        $dataDevis = [];

        foreach($devisStations as $devisStation) {
            $utilisateur = $devisStation->getUtilisateur();
            $dataDevis[] = [
                'idDevis' => $devisStation->getId(),
                'valeurArriver' => $devisStation->getValeurArriver(),
                'valeurDeDepart' => $devisStation->getValeurDeDepart(),
                'consommation' => $devisStation->getConsommation(),
                'prixUnite' => $devisStation->getPrixUnite(),
                'budgetObtenu' => $devisStation->getBudgetObtenu(),
                'dateAddDevis' => $devisStation->getDateAddDevis(),
                'utilisateur' => [
                    'id' => $utilisateur->getId(),
                    'nomUtilisateur' => $utilisateur->getNomUtilisateur(),
                    'prenomUtilisateur' => $utilisateur->getPrenomUtilisateur(),
                    'emailUtilisateur' => $utilisateur->getEmailUtilisateur(),
                    'motDePasse' => $utilisateur->getMotDePasse(),
                    'photoUrl' => $utilisateur->getPhotoUrl(),
                ]
            ];
        }
        return new JsonResponse($dataDevis, 200);
    }*/