<?php

namespace App\Controller;


use App\Entity\Voiture;
use App\Repository\VoitureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class VoitureController extends AbstractController
{
    #[Route('/api/voitures', methods: ['GET'])]
    public function getAllVoitures(VoitureRepository $voitureRepository): JsonResponse
    {
        $voitures = $voitureRepository->findAll();

        $data = array_map(fn(Voiture $voiture) => [
            'id' => $voiture->getId(),
            'marque' => $voiture->getMarque(),
            'modele' => $voiture->getModele(),
            'couleur' => $voiture->getCouleur(),
            'immatriculation' => $voiture->getvoiture(),
            'places' => $voiture->getPlace(),
            'image' => $voiture->getImage(),
            'user' => [
                'id' => $voiture->getProprietaire()->getId(),
                'email' => $voiture->getProprietaire()->getEmail(),
            ],
        ], $voitures);

        return $this->json($data);
    }

    #[Route('/api/voitures/{id}', methods: ['GET'])]
    public function getVoitureById(int $id, VoitureRepository $voitureRepository): JsonResponse
    {
        $voiture = $voitureRepository->find($id);

        if (!$voiture) {
            return $this->json(['error' => 'Voiture not found'], 404);
        }

        $data = [
            'id' => $voiture->getId(),
            'marque' => $voiture->getMarque(),
            'modele' => $voiture->getModele(),
            'couleur' => $voiture->getCouleur(),
            'immatriculation' => $voiture->getImmatriculation(),
            'places' => $voiture->getNombrePlaces(),
            'image' => $voiture->getImage(),
            'user' => [
                'id' => $voiture->getUser()->getId(),
                'email' => $voiture->getUser()->getEmail(),
            ],
        ];

        return $this->json($data);
    }

    #[Route('/api/voitures', methods: ['POST'])]
    public function createVoiture(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $this->getUser(); // Récupérer l'utilisateur connecté
        if (!$user) {
            return $this->json(['error' => 'User not authenticated'], 401);
        }

        $voiture = new Voiture();
        $voiture->setMarque($data['marque']);
        $voiture->setModele($data['modele']);
        $voiture->setCouleur($data['couleur']);
        $voiture->setvoiture($data['immatriculation']);
        $voiture->setPlace($data['places']);
        $voiture->setImage($data['image']);
        $voiture->setProprietaire($user);

        $em->persist($voiture);
        $em->flush();

        return $this->json(['status' => 'Voiture created'], 201);
    }

    #[Route('/api/voitures/{id}', methods: ['PUT'])]
    public function updateVoiture(int $id, Request $request, VoitureRepository $voitureRepository, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $voiture = $voitureRepository->find($id);

        if (!$voiture) {
            return $this->json(['error' => 'Voiture not found'], 404);
        }

        $voiture->setMarque($data['marque'] ?? $voiture->getMarque());
        $voiture->setModele($data['modele'] ?? $voiture->getModele());
        $voiture->setCouleur($data['couleur'] ?? $voiture->getCouleur());
        $voiture->setImmatriculation($data['immatriculation'] ?? $voiture->getImmatriculation());
        $voiture->setNombrePlaces($data['places'] ?? $voiture->getNombrePlaces());
        $voiture->setImage($data['image'] ?? $voiture->getImage());

        $em->flush();

        return $this->json(['status' => 'Voiture updated'], 200);
    }

    #[Route('/api/voitures/{id}', methods: ['DELETE'])]
    public function deleteVoiture(int $id, VoitureRepository $voitureRepository, EntityManagerInterface $em): JsonResponse
    {
        $voiture = $voitureRepository->find($id);

        if (!$voiture) {
            return $this->json(['error' => 'Voiture not found'], 404);
        }

        $em->remove($voiture);
        $em->flush();

        return $this->json(['status' => 'Voiture deleted'], 200);
    }
}