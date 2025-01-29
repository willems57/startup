<?php

namespace App\Controller;


use App\Entity\Avis;
use App\Repository\AvisRepository;
use App\Repository\TrajetsfiniRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;

class AvisController extends AbstractController
{

    /**
     * @OA\Get(
     *     path="/api/avis",
     *     summary="Récupère tous les avis",
     *     description="Retourne une liste de tous les avis présents dans la base de données.",
     *     @OA\Response(
     *         response=200,
     *         description="Liste des avis récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="commentaire", type="string", example="Très bon service"),
     *                 @OA\Property(property="note", type="integer", example=5),
     *                 @OA\Property(property="date", type="string", format="date", example="2025-01-01"),
     *                 @OA\Property(
     *                     property="trajetsfini",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=10),
     *                     @OA\Property(property="description", type="string", example="Paris -> Lyon"),
     *                     @OA\Property(property="date", type="string", format="date", example="2025-01-01")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    #[Route('/api/avis', methods: ['GET'])]
    public function getAllAvis(AvisRepository $avisRepository): JsonResponse
    {
        $avis = $avisRepository->findAll();

        $data = array_map(function (Avis $avis) {
            return [
                'id' => $avis->getId(),
                'commentaire' => $avis->getCommentaire(),
                'note' => $avis->getNote(),
                'date' => $avis->getcreatedAt()->format('Y-m-d'),
                'trajetsfini' => $avis->getConducteur() ? [
                    'id' => $avis->getConducteur()->getId(),
                    'description' => $avis->getConducteur()->getDepart(),
                    'date' => $avis->getConducteur()->getDate()->format('Y-m-d'),
                ] : null,
            ];
        }, $avis);

        return $this->json($data);
    }

    /**
     * @OA\Post(
     *     path="/api/avis",
     *     summary="Crée un nouvel avis",
     *     description="Ajoute un nouvel avis dans la base de données avec un trajet associé.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="commentaire", type="string", example="Très bon trajet."),
     *             @OA\Property(property="note", type="integer", example=4),
     *             @OA\Property(property="date", type="string", format="date", example="2025-01-01"),
     *             @OA\Property(property="trajetsfini_id", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Avis créé avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="Avis created")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Trajetsfini non trouvé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Trajetsfini not found")
     *         )
     *     )
     * )
     */
    #[Route('/api/avis', methods: ['POST'])]
    public function createAvis(
        Request $request,
        TrajetsfiniRepository $trajetsfiniRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $avis = new Avis();
        $avis->setCommentaire($data['commentaire']);
        $avis->setNote($data['note']);
        $avis->setcreatedAt(new \DateTimeImmutable($data['date']));

        if (isset($data['trajetsfini_id'])) {
            $trajetsfini = $trajetsfiniRepository->find($data['trajetsfini_id']);
            if (!$trajetsfini) {
                return $this->json(['error' => 'Trajetsfini not found'], 404);
            }
            $avis->setConducteur($trajetsfini);
        }

        $em->persist($avis);
        $em->flush();

        return $this->json(['status' => 'Avis created'], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/avis/{id}",
     *     summary="Met à jour un avis existant",
     *     description="Modifie les informations d'un avis existant.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Identifiant de l'avis à mettre à jour"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="commentaire", type="string", example="Service excellent."),
     *             @OA\Property(property="note", type="integer", example=5),
     *             @OA\Property(property="date", type="string", format="date", example="2025-01-01"),
     *             @OA\Property(property="trajetsfini_id", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Avis mis à jour avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="Avis updated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Avis ou trajetsfini non trouvé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Avis not found")
     *         )
     *     )
     * )
     */
    #[Route('/api/avis/{id}', methods: ['PUT'])]
    public function updateAvis(
        int $id,
        Request $request,
        AvisRepository $avisRepository,
        TrajetsfiniRepository $trajetsfiniRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $avis = $avisRepository->find($id);

        if (!$avis) {
            return $this->json(['error' => 'Avis not found'], 404);
        }

        $avis->setCommentaire($data['commentaire'] ?? $avis->getCommentaire());
        $avis->setNote($data['note'] ?? $avis->getNote());
        $avis->setDate(new \DateTime($data['date'] ?? $avis->getDate()->format('Y-m-d')));

        if (isset($data['trajetsfini_id'])) {
            $trajetsfini = $trajetsfiniRepository->find($data['trajetsfini_id']);
            if (!$trajetsfini) {
                return $this->json(['error' => 'Trajetsfini not found'], 404);
            }
            $avis->setTrajetfini($trajetsfini);
        }

        $em->flush();

        return $this->json(['status' => 'Avis updated'], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/avis/{id}",
     *     summary="Supprime un avis",
     *     description="Supprime un avis de la base de données.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Identifiant de l'avis à supprimer"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Avis supprimé avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="Avis deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Avis non trouvé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Avis not found")
     *         )
     *     )
     * )
     */
    #[Route('/api/avis/{id}', methods: ['DELETE'])]
    public function deleteAvis(
        int $id,
        AvisRepository $avisRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $avis = $avisRepository->find($id);

        if (!$avis) {
            return $this->json(['error' => 'Avis not found'], 404);
        }

        $em->remove($avis);
        $em->flush();

        return $this->json(['status' => 'Avis deleted'], 200);
    }
}