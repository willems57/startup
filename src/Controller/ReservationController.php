<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Trajets;
use App\Entity\User;
use App\Entity\Trajetsfini;
use App\Entity\Trajetsencours;
use App\Repository\ReservationRepository;
use App\Repository\TrajetsRepository;
use App\Repository\UserRepository;
use App\Repository\TrajetsfiniRepository;
use App\Repository\TrajetsencoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationController extends AbstractController
{
    #[Route('/api/reservations', methods: ['GET'])]
    public function getAllReservations(ReservationRepository $reservationRepository): JsonResponse
    {
        $reservations = $reservationRepository->findAll();

        $data = array_map(function (Reservation $reservation) {
            return [
                'id' => $reservation->getId(),
                'trajets' => $reservation->getTrajets() ? $this->getTrajetsData($reservation->getTrajets()) : null,
                'passager' => [
                    'id' => $reservation->getUser()->getId(),
                    'nom' => $reservation->getUser()->getfirstname(),
                    'email' => $reservation->getUser()->getEmail(),
                ],
                'trajetsfini' => $reservation->getTrajetfini() ? $this->getTrajetsFiniData($reservation->getTrajetfini()) : null,
                'trajetsencours' => $reservation->getTrajetsencours() ? $this->getTrajetsEncoursData($reservation->getTrajetsencours()) : null,
            ];
        }, $reservations);

        return $this->json($data);
    }

    #[Route('/api/reservations', methods: ['POST'])]
    public function createReservation(
        Request $request,
        TrajetsRepository $trajetsRepository,
        UserRepository $userRepository,
        TrajetsfiniRepository $trajetsfiniRepository,
        TrajetsencoursRepository $trajetsencoursRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $trajets = $trajetsRepository->find($data['trajets_id']);
        if (!$trajets) {
            return $this->json(['error' => 'Trajets not found'], 404);
        }

        $user = $userRepository->find($data['user_id']);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        if ($trajets->getPlacesDisponibles() <= 0) {
            return $this->json(['error' => 'No available seats on this trajet'], 400);
        }

        $reservation = new Reservation();
        $reservation->setTrajets($trajets);
        $reservation->setUser($user);

        if (isset($data['trajetsfini_id'])) {
            $trajetsfini = $trajetsfiniRepository->find($data['trajetsfini_id']);
            if (!$trajetsfini) {
                return $this->json(['error' => 'Trajetsfini not found'], 404);
            }
            $reservation->setTrajetfini($trajetsfini);
        }

        if (isset($data['trajetsencours_id'])) {
            $trajetsencours = $trajetsencoursRepository->find($data['trajetsencours_id']);
            if (!$trajetsencours) {
                return $this->json(['error' => 'Trajetsencours not found'], 404);
            }
            $reservation->setTrajetsencours($trajetsencours);
        }

        $trajets->setPlacesDisponibles($trajets->getPlacesDisponibles() - 1);

        $em->persist($reservation);
        $em->flush();

        return $this->json(['status' => 'Reservation created'], 201);
    }

    #[Route('/api/reservations/{id}', methods: ['PUT'])]
    public function updateReservation(
        int $id,
        Request $request,
        ReservationRepository $reservationRepository,
        TrajetsfiniRepository $trajetsfiniRepository,
        TrajetsencoursRepository $trajetsencoursRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            return $this->json(['error' => 'Reservation not found'], 404);
        }

        if (isset($data['trajetsfini_id'])) {
            $trajetsfini = $trajetsfiniRepository->find($data['trajetsfini_id']);
            if (!$trajetsfini) {
                return $this->json(['error' => 'Trajetsfini not found'], 404);
            }
            $reservation->setTrajetsfini($trajetsfini);
        }

        if (isset($data['trajetsencours_id'])) {
            $trajetsencours = $trajetsencoursRepository->find($data['trajetsencours_id']);
            if (!$trajetsencours) {
                return $this->json(['error' => 'Trajetsencours not found'], 404);
            }
            $reservation->setTrajetsencours($trajetsencours);
        }
       

        $em->flush();

        return $this->json(['status' => 'Reservation updated'], 200);
    }

    #[Route('/api/reservations/{id}', methods: ['DELETE'])]
    public function deleteReservation(
        int $id,
        ReservationRepository $reservationRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            return $this->json(['error' => 'Reservation not found'], 404);
        }

        $trajet = $reservation->getTrajets();
        $trajet->setPlacesDisponibles($trajet->getPlacesDisponibles() + 1);

        $em->remove($reservation);
        $em->flush();

        return $this->json(['status' => 'Reservation deleted'], 200);
    }

    private function getTrajetsData(Trajets $trajets): array
    {
        return [
            'id' => $trajets->getId(),
            'depart' => $trajets->getDepart(),
            'arrive' => $trajets->getArrive(),
            'date' => $trajets->getDate()->format('Y-m-d H:i:s'),
        ];
    }

    private function getTrajetsFiniData(Trajetsfini $trajetsfini): array
    {
        return [
            'id' => $trajetsfini->getId(),
            'depart' => $trajetsfini->getDepart(),
            'arrive' => $trajetsfini->getArrive(),
            'date' => $trajetsfini->getDate()->format('Y-m-d H:i:s'),
        ];
    }

    private function getTrajetsEncoursData(Trajetsencours $trajetsencours): array
    {
        return [
            'id' => $trajetsencours->getId(),
            'depart' => $trajetsencours->getDepart(),
            'arrive' => $trajetsencours->getArrive(),
            'date' => $trajetsencours->getDate()->format('Y-m-d H:i:s'),
        ];
    }
}