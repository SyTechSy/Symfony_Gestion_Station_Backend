<?php

namespace App\Controller;

use App\Entity\DevisStationGasoil;
use App\Entity\Utilisateur;
use App\Repository\DevisStationGasoilRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class DevisGasoilController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UtilisateurRepository $utilisateurRepository;

    public function __construct(EntityManagerInterface $entityManager, UtilisateurRepository $utilisateurRepository)
    {
        $this->entityManager = $entityManager;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    /**
     * @throws \Exception
     */
    #[Route('/add/devisGasoil', name: 'create_devis_gasoil', methods: ['POST'])]
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

        $devisStation = new DevisStationGasoil();
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
    #[Route('/list/devisGasoil/{id}', name: 'list_devis_gasoil', methods: ['GET'])]
    public function listDevis(int $id): JsonResponse
    {
        // Vérifier si l'utilisateur existe
        $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($id);
        if (!$utilisateur) {
            throw new NotFoundHttpException("L'utilisateur avec l'ID $id n'existe pas.");
        }

        // Récupérer les devis associés à l'utilisateur
        $devisStationsGasoil = $this->entityManager->getRepository(DevisStationGasoil::class)->findBy(['utilisateur' => $id]);

        // Si l'utilisateur n'a pas créé de devis
        if (empty($devisStationsGasoil)) {
            return new JsonResponse(['message' => "L'utilisateur avec l'ID $id n'a pas créé de devis Gasoil."], 404);
        }

        // Construire la réponse avec les devis trouvés
        $dataDeviss = [];
        foreach ($devisStationsGasoil as $devisStation) {
            array_unshift($dataDeviss, [
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

        return new JsonResponse($dataDeviss, 200);
    }

    //SUPPRESSION UN DEVIS Gasoil
    #[Route('/delete/devisGasoil/{id}', name: 'devisGasoil_delete', methods: ['DELETE'])]
    public function deleteDevisGasoil(int $id): JsonResponse
    {
        $devisStationGasoil = $this->entityManager->getRepository(DevisStationGasoil::class)->find($id);

        if (!$devisStationGasoil) {
            return new JsonResponse(['message' => 'Devis gasoil non trouvé'], 404);
        }

        $this->entityManager->remove($devisStationGasoil);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Devis gasoil supprimé avec succès'], 200);
    }

    // MODIFICATION DEVIS GASOIL
    #[Route('/edit/devisGasoil/{id}', name: 'devisGasoil_update', methods: ['PUT'])]
    public function updateDevisGasoil(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        DevisStationGasoilRepository $devisStationGasoilRepository): JsonResponse
    {
        $dataGasoil = json_decode($request->getContent(), true);
        error_log(print_r($dataGasoil, true));

        // Trouve le devis gasoil par ID
        $devisStation = $devisStationGasoilRepository->find($id);
        if (!$devisStation) {
            return $this->json(['error' => 'Devis gasoil non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $valeurArriver = $dataGasoil['valeurArriver'];
        $valeurDeDepart = $dataGasoil['valeurDeDepart'];
        $prixUnite = $dataGasoil['prixUnite'];

        // Recalcule la consommation et le budget
        $consommation = $valeurArriver - $valeurDeDepart;
        $budgetObtenu = $consommation * $prixUnite;

        // Met à jour les champs du devis gasoil
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
