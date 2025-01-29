<?php

namespace App\Controller;


use App\Entity\Avisvalidation;
use App\Entity\Trajetsfini;
use App\Repository\AvisvalidationRepository;
use App\Repository\TrajetsfiniRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class AvisvalidationController extends AbstractController
{
    #[Route('/api/avisvalidation', methods: ['GET'])]
    public function getAllAvis(AvisvalidationRepository $avisvalidationRepository): JsonResponse
    {
        $avisvalidation = $avisvalidationRepository->findAll();

        $data = array_map(function (Avisvalidation $avisvalidation) {
            return [
                'id' => $avisvalidation->getId(),
                'commentaire' => $avisvalidation->getCommentaire(),
                'note' => $avisvalidation->getNote(),
                'date' => $avisvalidation->getcreatedAt()->format('Y-m-d'),
                'trajetsfini' => $avisvalidation->getConducteur() ? [
                    'id' => $avisvalidation->getConducteur()->getId(),
                    'description' => $avisvalidation->getConducteur()->getDepart(),
                    'date' => $avisvalidation->getConducteur()->getDate()->format('Y-m-d'),
                ] : null,
            ];
        }, $avisvalidation);

        return $this->json($data);
    }

    #[Route('/api/avisvalidation', methods: ['POST'])]
    public function createAvis(
        Request $request,
        TrajetsfiniRepository $trajetsfiniRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $avisvalidation = new Avisvalidation();
        $avisvalidation->setCommentaire($data['commentaire']);
        $avisvalidation->setNote($data['note']);
        $avisvalidation->setcreatedAt(new \DateTimeImmutable($data['date']));

        if (isset($data['trajetsfini_id'])) {
            $trajetsfini = $trajetsfiniRepository->find($data['trajetsfini_id']);
            if (!$trajetsfini) {
                return $this->json(['error' => 'Trajetsfini not found'], 404);
            }
            $avisvalidation->setConducteur($trajetsfini);
        }

        $em->persist($avisvalidation);
        $em->flush();

        return $this->json(['status' => 'Avisvalidation created'], 201);
    }

    #[Route('/api/avisvalidation/{id}', methods: ['PUT'])]
    public function updateAvisvalidation(
        int $id,
        Request $request,
        AvisvalidationRepository $avisvalidationRepository,
        TrajetsfiniRepository $trajetsfiniRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $avisvalidation = $avisvalidationRepository->find($id);

        if (!$avisvalidation) {
            return $this->json(['error' => 'Avis not found'], 404);
        }

        $avisvalidation->setCommentaire($data['commentaire'] ?? $avisvalidation->getCommentaire());
        $avisvalidation->setNote($data['note'] ?? $avisvalidation->getNote());
        $avisvalidation->setDate(new \DateTime($data['date'] ?? $avisvalidation->getDate()->format('Y-m-d')));

        if (isset($data['trajetsfini_id'])) {
            $trajetsfini = $trajetsfiniRepository->find($data['trajetsfini_id']);
            if (!$trajetsfini) {
                return $this->json(['error' => 'Trajetsfini not found'], 404);
            }
            $avisvalidation->setTrajetfini($trajetsfini);
        }

        $em->flush();

        return $this->json(['status' => 'Avisvalidation updated'], 200);
    }

    #[Route('/api/avisvalidation/{id}', methods: ['DELETE'])]
    public function deleteAvisvalidation(
        int $id,
        AvisvalidationRepository $avisvalidationRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $avisvalidation = $avisvalidationRepository->find($id);

        if (!$avisvalidation) {
            return $this->json(['error' => 'Avisvalidation not found'], 404);
        }

        $em->remove($avisvalidation);
        $em->flush();

        return $this->json(['status' => 'Avisvalidation deleted'], 200);
    }
}