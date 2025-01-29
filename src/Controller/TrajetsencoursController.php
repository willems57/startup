<?php

namespace App\Controller;


use App\Entity\Trajetsencours;
use App\Entity\Voiture;
use App\Entity\User;
use App\Repository\TrajetsencoursRepository;
use App\Repository\VoitureRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class TrajetsencoursController extends AbstractController
{
    #[Route('/api/trajetsencours', methods: ['GET'])]
    public function getAllTrajetsencours(TrajetsencoursRepository $trajetsencoursRepository): JsonResponse
    {
        $trajetsencours = $trajetsencoursRepository->findAll();

        $data = array_map(function (Trajetsencours $trajetsencours) {
            return [
                'id' => $trajetsencours->getId(),
                'depart' => $trajetsencours->getDepart(),
                'arrive' => $trajetsencours->getArrive(),
                'date' => $trajetsencours->getDate()->format('Y-m-d H:i:s'),
                'duree' => $trajetsencours->getDuree(),
                'prix' => $trajetsencours->getPrix(),
                
                'conducteur' => [
                    'id' => $trajetsencours->getConducteur()->getId(),
                    'nom' => $trajetsencours->getConducteur()->getfirstName(),
                    'email' => $trajetsencours->getConducteur()->getEmail(),
                ],
                'voiture' => [
                    'id' => $trajetsencours->getVoiture()->getId(),
                    'marque' => $trajetsencours->getVoiture()->getMarque(),
                    'modele' => $trajetsencours->getVoiture()->getModele(),
                    'place' => $trajetsencours->getVoiture()->getPlace(),
                ],
                'passagers' => array_map(function ($reservation) {
                    return [
                        'id' => $reservation->getId(),
                        'nom_passager' => $reservation->getNomPassager(),
                    ];
                }, $trajetsencours->getPassager()->toArray()),
            ];
        }, $trajetsencours);

        return $this->json($data);
    }

    #[Route('/api/trajetsencours', methods: ['POST'])]
    public function createTrajetsencours(
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

        $trajetsencours = new Trajetsencours();
        $trajetsencours->setDepart($data['depart']);
        $trajetsencours->setArrive($data['arrive']);
        $trajetsencours->setDate(new \DateTime($data['date']));
        $trajetsencours->setDuree($data['duree']);
        $trajetsencours->setPrix($data['prix']);
        $trajetsencours->setVoiture($voiture);
        $trajetsencours->setConducteur($conducteur);

        $em->persist($trajetsencours);
        $em->flush();

        return $this->json(['status' => 'Trajetsencours created'], 201);
    }

    #[Route('/api/trajetsencours/{id}', methods: ['PUT'])]
    public function updateTrajetsencours(
        int $id,
        Request $request,
        TrajetsencoursRepository $trajetsencoursRepository,
        VoitureRepository $voitureRepository,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $trajetsencours = $trajetsencoursRepository->find($id);

        if (!$trajetsencours) {
            return $this->json(['error' => 'Trajetsencours not found'], 404);
        }

        if (isset($data['voiture_id'])) {
            $voiture = $voitureRepository->find($data['voiture_id']);
            if (!$voiture) {
                return $this->json(['error' => 'Voiture not found'], 404);
            }
            $trajetsencours->setVoiture($voiture);
        }

        if (isset($data['conducteur_id'])) {
            $conducteur = $userRepository->find($data['conducteur_id']);
            if (!$conducteur) {
                return $this->json(['error' => 'Conducteur not found'], 404);
            }
            $trajetsencours->setConducteur($conducteur);
        }

        $trajetsencours->setDepart($data['depart'] ?? $trajetsencours->getDepart());
        $trajetsencours->setArrive($data['arrive'] ?? $trajetsencours->getArrive());
        $trajetsencours->setDate(new \DateTime($data['date'] ?? $trajetsencours->getDate()->format('Y-m-d H:i:s')));
        $trajetsencours->setDuree($data['duree'] ?? $trajetsencours->getDuree());
        $trajetsencours->setPrix($data['prix'] ?? $trajetsencours->getPrix());
        $trajetsencours->setPlacesDisponibles($data['places_disponibles'] ?? $trajetsencours->getPlacesDisponibles());

        $em->flush();

        return $this->json(['status' => 'Trajets updated'], 200);
    }

    #[Route('/api/trajetsencours/{id}', methods: ['DELETE'])]
    public function deleteTrajetsencours(int $id, TrajetsencoursRepository $trajetsencoursRepository, EntityManagerInterface $em): JsonResponse
    {
        $trajetsencours = $trajetsencoursRepository->find($id);

        if (!$trajetsencours) {
            return $this->json(['error' => 'Trajetsencours not found'], 404);
        }

        $em->remove($trajetsencours);
        $em->flush();

        return $this->json(['status' => 'Trajetsencours deleted'], 200);
    }
}