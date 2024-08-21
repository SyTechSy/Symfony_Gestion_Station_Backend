<?php

namespace App\Controller;

use App\Entity\BudgetSommeTotal;
use App\Entity\DevisStation;
use App\Entity\Utilisateur;
use App\Repository\BudgetSommeTotalRepository;
use App\Repository\DevisStationRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class BudgetSommeTotalController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UtilisateurRepository $utilisateurRepository;

    public function __construct(EntityManagerInterface $entityManager, UtilisateurRepository $utilisateurRepository)
    {
        $this->entityManager = $entityManager;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    // POUR LA CREATION DE DES BUDGETS TOTAL
    #[Route('/create/budgets_total', name: 'create_budget_total', methods: ['POST'])]
    public function createBudgetTotal(
        Request $request, EntityManagerInterface $entityManager,
        UtilisateurRepository $utilisateurRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $budgetTotalEssence = $data['budgetTotalEssence'];
        $budgetTotalGasoil = $data['budgetTotalGasoil'];
        $argentRecuTravail = $data['argentRecuTravail'];
        $idUser = $data['utilisateur']['id'];

        $utilisateur = $utilisateurRepository->find($idUser);
        if (!$utilisateur) {
            return $this->json(['error' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $budgetSommeTotal = new BudgetSommeTotal();

        // Calculer la somme des budgets
        $sommeTotalBudgets = $budgetTotalEssence + $budgetTotalGasoil;
        $budgetSommeTotal->setSommeTotalBudgets($sommeTotalBudgets);

        // Calculer la perte ou le gain
        if ($argentRecuTravail < $sommeTotalBudgets) {
            $perteArgent = $argentRecuTravail - $sommeTotalBudgets;
            $gainArgent = 0;
        } else {
            $perteArgent = 0;
            $gainArgent = $argentRecuTravail - $sommeTotalBudgets;
        }

        $budgetSommeTotal->setPerteArgent($perteArgent);
        $budgetSommeTotal->setGainArgent($gainArgent);
        $budgetSommeTotal->setBudgetTotalEssence($budgetTotalEssence);
        $budgetSommeTotal->setBudgetTotalGasoil($budgetTotalGasoil);
        $budgetSommeTotal->setArgentRecuTravail($argentRecuTravail);
        $budgetSommeTotal->setDateAddBudgetTotal(new \DateTime());
        $budgetSommeTotal->setUtilisateur($utilisateur);

        $entityManager->persist($budgetSommeTotal);
        $entityManager->flush();

        return new JsonResponse([
            'idBudgetTotal' => $budgetSommeTotal->getId(),
            'budgetTotalEssence' => $budgetSommeTotal->getBudgetTotalEssence(),
            'budgetTotalGasoil' => $budgetSommeTotal->getBudgetTotalGasoil(),
            'sommeTotalBudgets' => $budgetSommeTotal->getSommeTotalBudgets(),
            'argentRecuTravail' => $budgetSommeTotal->getArgentRecuTravail(),
            'perteArgent' => $budgetSommeTotal->getPerteArgent(),
            'gainArgent' => $budgetSommeTotal->getGainArgent(),
            'dateAddBudgetTotal' => $budgetSommeTotal->getDateAddBudgetTotal()->format('Y-m-d H:i:s'),
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

    // POUR LES LISTES DES BUDGET TOTAL
    #[Route('/list/budgetTotal/{id}', name: 'list_budget_total', methods: ['GET'])]
    public function listBudgetTotal(int $id): JsonResponse
    {
        // Vérifier si l'utilisateur existe
        $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($id);
        if (!$utilisateur) {
            throw new NotFoundHttpException("L'utilisateur avec l'ID $id n'existe pas.");
        }

        // Récupérer les devis associés à l'utilisateur
        $budgetSommeTotals = $this->entityManager->getRepository(BudgetSommeTotal::class)->findBy(['utilisateur' => $id]);

        // Si l'utilisateur n'a pas créé de devis
        if (empty($budgetSommeTotals)) {
            return new JsonResponse(['message' => "L'utilisateur avec l'ID $id n'a pas créé de budget total."], 404);
        }

        // Construire la réponse avec les devis trouvés
        $dataDevis = [];
        foreach ($budgetSommeTotals as $budgetSommeTotal) {
            array_unshift($dataDevis, [
                'idBudgetTotal' => $budgetSommeTotal->getId(),
                'budgetTotalEssence' => $budgetSommeTotal->getBudgetTotalEssence(),
                'budgetTotalGasoil' => $budgetSommeTotal->getBudgetTotalGasoil(),
                'sommeTotalBudgets' => $budgetSommeTotal->getSommeTotalBudgets(),
                'argentRecuTravail' => $budgetSommeTotal->getArgentRecuTravail(),
                'perteArgent' => $budgetSommeTotal->getPerteArgent(),
                'gainArgent' => $budgetSommeTotal->getGainArgent(),
                'dateAddBudgetTotal' => $budgetSommeTotal->getDateAddBudgetTotal(),
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

    // SUPPRESSION DE BUDGET TOTAL
    #[Route('/delete/budgetTotal/{id}', name: 'budget_total_delete', methods: ['DELETE'])]
    public function deleteBudgetTotal(int $id): JsonResponse
    {
        $budgetSommeTotal = $this->entityManager->getRepository(BudgetSommeTotal::class)->find($id);

        if (!$budgetSommeTotal) {
            return new JsonResponse(['message' => 'Budget total non trouvé'], 404);
        }

        $this->entityManager->remove($budgetSommeTotal);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Budget total supprimé avec succès'], 200);
    }

    // POUR LA MODIFICATION DE BUDGET TOTAL
    #[Route('/edit/budgetTotal/{id}', name: 'budget_total_update', methods: ['PUT'])]
    public function updateBudgetTotal(
        int $id, Request $request, EntityManagerInterface $entityManager,
        BudgetSommeTotalRepository $budgetSommeTotalRepository): JsonResponse
    {
        $dataBudget = json_decode($request->getContent(), true);
        error_log(print_r($dataBudget, true));

        // Trouve le  essence par ID
        $budgetSommeTotal = $budgetSommeTotalRepository->find($id);
        if (!$budgetSommeTotal) {
            return $this->json(['error' => 'Budget total non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $budgetTotalEssence = $dataBudget['budgetTotalEssence'];
        $budgetTotalGasoil = $dataBudget['budgetTotalGasoil'];
        $argentRecuTravail = $dataBudget['argentRecuTravail'];
        $idUser = $dataBudget['utilisateur']['id'];

        // Recalcule la consommation et le budget
        $sommeTotalBudgets = $budgetTotalEssence + $budgetTotalGasoil;
        $budgetSommeTotal->setSommeTotalBudgets($sommeTotalBudgets);

        // Calculer la perte ou le gain
        if ($argentRecuTravail < $sommeTotalBudgets) {
            $perteArgent = $argentRecuTravail - $sommeTotalBudgets;
            $gainArgent = 0;
        } else {
            $perteArgent = 0;
            $gainArgent = $argentRecuTravail - $sommeTotalBudgets;
        }

        // Met à jour les champs du devis essence
        $budgetSommeTotal->setPerteArgent($perteArgent);
        $budgetSommeTotal->setGainArgent($gainArgent);
        $budgetSommeTotal->setBudgetTotalEssence($budgetTotalEssence);
        $budgetSommeTotal->setBudgetTotalGasoil($budgetTotalGasoil);
        $budgetSommeTotal->setArgentRecuTravail($argentRecuTravail);
        $budgetSommeTotal->setDateAddBudgetTotal(new \DateTime());

        $entityManager->persist($budgetSommeTotal);
        $entityManager->flush();

        return new JsonResponse([
            'idBudgetTotal' => $budgetSommeTotal->getId(),
            'budgetTotalEssence' => $budgetSommeTotal->getBudgetTotalEssence(),
            'budgetTotalGasoil' => $budgetSommeTotal->getBudgetTotalGasoil(),
            'sommeTotalBudgets' => $budgetSommeTotal->getSommeTotalBudgets(),
            'argentRecuTravail' => $budgetSommeTotal->getArgentRecuTravail(),
            'perteArgent' => $budgetSommeTotal->getPerteArgent(),
            'gainArgent' => $budgetSommeTotal->getGainArgent(),
            'dateAddBudgetTotal' => $budgetSommeTotal->getDateAddBudgetTotal()->format('Y-m-d H:i:s'),
            'utilisateur' => [
                'id' => $budgetSommeTotal->getUtilisateur()->getId(),
                'nomUtilisateur' => $budgetSommeTotal->getUtilisateur()->getNomUtilisateur(),
                'prenomUtilisateur' => $budgetSommeTotal->getUtilisateur()->getPrenomUtilisateur(),
                'emailUtilisateur' => $budgetSommeTotal->getUtilisateur()->getEmailUtilisateur(),
                'motDePasse' => $budgetSommeTotal->getUtilisateur()->getMotDePasse(),
                'photoUrl' => $budgetSommeTotal->getUtilisateur()->getPhotoUrl(),
            ]
        ], Response::HTTP_OK);
    }
}
