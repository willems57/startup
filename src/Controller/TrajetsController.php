<?php

namespace App\Controller;


use App\Entity\Trajets;
use App\Entity\Voiture;
use App\Entity\User;
use App\Repository\TrajetsRepository;
use App\Repository\VoitureRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class TrajetsController extends AbstractController
{
    #[Route('/api/trajets', methods: ['GET'])]
    public function getAllTrajets(TrajetsRepository $trajetsRepository): JsonResponse
    {
        $trajets = $trajetsRepository->findAll();

        $data = array_map(function (Trajets $trajets) {
            return [
                'id' => $trajets->getId(),
                'depart' => $trajets->getDepart(),
                'arrive' => $trajets->getArrive(),
                'date' => $trajets->getDate()->format('Y-m-d H:i:s'),
                'duree' => $trajets->getDuree(),
                'prix' => $trajets->getPrix(),
                
                'conducteur' => [
                    'id' => $trajets->getConducteur()->getId(),
                    'nom' => $trajets->getConducteur()->getfirstName(),
                    'email' => $trajets->getConducteur()->getEmail(),
                ],
                'voiture' => [
                    'id' => $trajets->getVoiture()->getId(),
                    'marque' => $trajets->getVoiture()->getMarque(),
                    'modele' => $trajets->getVoiture()->getModele(),
                    'place' => $trajets->getVoiture()->getPlace(),
                ],
                'passagers' => array_map(function ($reservation) {
                    return [
                        'id' => $reservation->getId(),
                        'nom_passager' => $reservation->getNomPassager(),
                    ];
                }, $trajets->getPassager()->toArray()),
            ];
        }, $trajets);

        return $this->json($data);
    }

    #[Route('/api/trajets', methods: ['POST'])]
    public function createTrajets(
        Request $request,
        VoitureRepository $voitureRepository,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $voiture = $voitureRepository->find($data['voiture_id']);
        if (!$voiture) {
            return $this->json(['error' => 'Voiture not found'], 404);
        }

        $conducteur = $userRepository->find($data['conducteur_id']);
        if (!$conducteur) {
            return $this->json(['error' => 'Conducteur not found'], 404);
        }

        $trajets = new Trajets();
        $trajets->setDepart($data['depart']);
        $trajets->setArrive($data['arrive']);
        $trajets->setDate(new \DateTime($data['date']));
        $trajets->setDuree($data['duree']);
        $trajets->setPrix($data['prix']);
        $trajets->setVoiture($voiture);
        $trajets->setConducteur($conducteur);

        $em->persist($trajets);
        $em->flush();

        return $this->json(['status' => 'Trajets created'], 201);
    }

    #[Route('/api/trajets/{id}', methods: ['PUT'])]
    public function updateTrajets(
        int $id,
        Request $request,
        TrajetsRepository $trajetsRepository,
        VoitureRepository $voitureRepository,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $trajets = $trajetsRepository->find($id);

        if (!$trajets) {
            return $this->json(['error' => 'Trajets not found'], 404);
        }

        if (isset($data['voiture_id'])) {
            $voiture = $voitureRepository->find($data['voiture_id']);
            if (!$voiture) {
                return $this->json(['error' => 'Voiture not found'], 404);
            }
            $trajets->setVoiture($voiture);
        }

        if (isset($data['conducteur_id'])) {
            $conducteur = $userRepository->find($data['conducteur_id']);
            if (!$conducteur) {
                return $this->json(['error' => 'Conducteur not found'], 404);
            }
            $trajets->setConducteur($conducteur);
        }

        $trajets->setDepart($data['depart'] ?? $trajets->getDepart());
        $trajets->setArrive($data['arrive'] ?? $trajets->getArrive());
        $trajets->setDate(new \DateTime($data['date'] ?? $trajets->getDate()->format('Y-m-d H:i:s')));
        $trajets->setDuree($data['duree'] ?? $trajets->getDuree());
        $trajets->setPrix($data['prix'] ?? $trajets->getPrix());
        $trajets->setPlacesDisponibles($data['places_disponibles'] ?? $trajets->getPlacesDisponibles());

        $em->flush();

        return $this->json(['status' => 'Trajets updated'], 200);
    }

    #[Route('/api/trajets/{id}', methods: ['DELETE'])]
    public function deleteTrajets(int $id, TrajetsRepository $trajetsRepository, EntityManagerInterface $em): JsonResponse
    {
        $trajets = $trajetsRepository->find($id);

        if (!$trajets) {
            return $this->json(['error' => 'Trajets not found'], 404);
        }

        $em->remove($trajets);
        $em->flush();

        return $this->json(['status' => 'Trajets deleted'], 200);
    }
}