<?php

namespace App\Controller;

use App\Entity\Trajetsfini;
use App\Entity\Voiture;
use App\Entity\User;
use App\Repository\TrajetsfiniRepository;
use App\Repository\VoitureRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrajetsfiniController extends AbstractController
{
    #[Route('/api/trajetsfini', methods: ['GET'])]
    public function getAllTrajetsfini(TrajetsfiniRepository $trajetsfiniRepository): JsonResponse
    {
        $trajetsfini = $trajetsfiniRepository->findAll();

        $data = array_map(function (Trajetsfini $trajetsfini) {
            return [
                'id' => $trajetsfini->getId(),
                'depart' => $trajetsfini->getDepart(),
                'arrive' => $trajetsfini->getArrive(),
                'date' => $trajetsfini->getDate()->format('Y-m-d H:i:s'),
                'duree' => $trajetsfini->getDuree(),
                'prix' => $trajetsfini->getPrix(),
                
                'conducteur' => [
                    'id' => $trajetsfini->getConducteur()->getId(),
                    'nom' => $trajetsfini->getConducteur()->getfirstName(),
                    'email' => $trajetsfini->getConducteur()->getEmail(),
                ],
                'voiture' => [
                    'id' => $trajetsfini->getVoiture()->getId(),
                    'marque' => $trajetsfini->getVoiture()->getMarque(),
                    'modele' => $trajetsfini->getVoiture()->getModele(),
                    'place' => $trajetsfini->getVoiture()->getPlace(),
                ],
                'passagers' => array_map(function ($reservation) {
                    return [
                        'id' => $reservation->getId(),
                        'nom_passager' => $reservation->getNomPassager(),
                    ];
                }, $trajetsfini->getPassager()->toArray()),
            ];
        }, $trajetsfini);

        return $this->json($data);
    }

    #[Route('/api/trajetsfini', methods: ['POST'])]
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

        $trajetsfini = new Trajetsfini();
        $trajetsfini->setDepart($data['depart']);
        $trajetsfini->setArrive($data['arrive']);
        $trajetsfini->setDate(new \DateTime($data['date']));
        $trajetsfini->setDuree($data['duree']);
        $trajetsfini->setPrix($data['prix']);
        $trajetsfini->setVoiture($voiture);
        $trajetsfini->setConducteur($conducteur);

        $em->persist($trajetsfini);
        $em->flush();

        return $this->json(['status' => 'Trajetsfini created'], 201);
    }

    #[Route('/api/trajetsfini/{id}', methods: ['PUT'])]
    public function updateTrajetsfini(
        int $id,
        Request $request,
        TrajetsfiniRepository $trajetsfiniRepository,
        VoitureRepository $voitureRepository,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $trajetsfini = $trajetsfiniRepository->find($id);

        if (!$trajetsfini) {
            return $this->json(['error' => 'Trajetsfini not found'], 404);
        }

        if (isset($data['voiture_id'])) {
            $voiture = $voitureRepository->find($data['voiture_id']);
            if (!$voiture) {
                return $this->json(['error' => 'Voiture not found'], 404);
            }
            $trajetsfini->setVoiture($voiture);
        }

        if (isset($data['conducteur_id'])) {
            $conducteur = $userRepository->find($data['conducteur_id']);
            if (!$conducteur) {
                return $this->json(['error' => 'Conducteur not found'], 404);
            }
            $trajetsfini->setConducteur($conducteur);
        }

        $trajetsfini->setDepart($data['depart'] ?? $trajetsfini->getDepart());
        $trajetsfini->setArrive($data['arrive'] ?? $trajetsfini->getArrive());
        $trajetsfini->setDate(new \DateTime($data['date'] ?? $trajetsfini->getDate()->format('Y-m-d H:i:s')));
        $trajetsfini->setDuree($data['duree'] ?? $trajetsfini->getDuree());
        $trajetsfini->setPrix($data['prix'] ?? $trajetsfini->getPrix());
        $trajetsfini->setPlacesDisponibles($data['places_disponibles'] ?? $trajetsfini->getPlacesDisponibles());

        $em->flush();

        return $this->json(['status' => 'Trajetsfini updated'], 200);
    }

    #[Route('/api/trajetsfini/{id}', methods: ['DELETE'])]
    public function deleteTrajets(int $id, TrajetsfiniRepository $trajetsfiniRepository, EntityManagerInterface $em): JsonResponse
    {
        $trajetsfini = $trajetsfiniRepository->find($id);

        if (!$trajetsfini) {
            return $this->json(['error' => 'Trajetsfini not found'], 404);
        }

        $em->remove($trajetsfini);
        $em->flush();

        return $this->json(['status' => 'Trajetsfini deleted'], 200);
    }
}