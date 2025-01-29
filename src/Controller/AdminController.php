<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/users')]
class AdminController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function listUsers(UserRepository $userRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        $users = $userRepository->findAll();

        $data = array_map(fn($user) => [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ], $users);

        return $this->json($data);
    }

    #[Route('/{id}/role', methods: ['PUT'])]
    public function changeUserRole(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        $data = json_decode($request->getContent(), true);

        if (isset($data['roles'])) {
            $user->setRoles($data['roles']);
            $entityManager->flush();

            return $this->json(['message' => 'Rôles mis à jour avec succès.']);
        }

        return $this->json(['error' => 'Aucun rôle fourni.'], 400);
    }

    
#[Route('/search', methods: ['POST'])]
public function searchUsers(Request $request, UserRepository $userRepository): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $criteria = array_filter([
        'firstName' => $data['firstName'] ?? null,
        'lastName' => $data['lastName'] ?? null,
        'email' => $data['email'] ?? null,
    ]);

    $users = $userRepository->findBy($criteria);

    $data = array_map(fn($user) => [
        'id' => $user->getId(),
        'email' => $user->getEmail(),
        'roles' => $user->getRoles(),
    ], $users);

    return $this->json($data);
}
}
