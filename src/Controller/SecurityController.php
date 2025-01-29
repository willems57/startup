<?php

namespace App\Controller;


use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;



#[Route('/api/security', name: 'api_security_')]
class SecurityController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }


    /**
 * @OA\Post(
 *     path="/api/security/registration",
 *     summary="Enregistre un nouvel utilisateur",
 *     description="Permet de créer un compte utilisateur avec un rôle par défaut ou spécifié.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="email", type="string", example="user@example.com"),
 *             @OA\Property(property="password", type="string", example="password123"),
 *             @OA\Property(property="firstName", type="string", example="John"),
 *             @OA\Property(property="lastName", type="string", example="Doe"),
 *             @OA\Property(property="credits", type="integer", example=20),
 *             @OA\Property(
 *                 property="roles",
 *                 type="array",
 *                 @OA\Items(type="string"),
 *                 example={"ROLE_USER"}
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Utilisateur enregistré avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="User registered successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Erreur de validation des données",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="errors", type="string", example="Invalid data")
 *         )
 *     )
 * )
 */


    #[Route('/registration', name: 'registration', methods: ['POST'])]
    public function register(
        Request $request,
        ValidatorInterface $validator,
        UserRepository $userRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Validation des données obligatoires
        if (!isset($data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Email and password are required'], Response::HTTP_BAD_REQUEST);
        }

        // Vérification des doublons
        if ($userRepository->findOneBy(['email' => $data['email']])) {
            return new JsonResponse(['error' => 'This email is already registered'], Response::HTTP_CONFLICT);
        }

        // Rôles valides disponibles
        $validRoles = ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_EMPLOYER', "ROLE_SUSPENDED"];

        // Validation des rôles fournis
        $roles = $data['roles'] ?? ['ROLE_USER']; // Rôle par défaut : ROLE_USER
        foreach ($roles as $role) {
            if (!in_array($role, $validRoles, true)) {
                return new JsonResponse(['error' => "Invalid role provided: $role"], Response::HTTP_BAD_REQUEST);
            }
        }

        // Création de l'utilisateur
        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        $user->setRoles($roles);
        $user->setFirstName($data['firstName'] ?? null);
        $user->setLastName($data['lastName'] ?? null);
        $user->setCredits($data['credits'] ?? 20);
        $user->setApiToken(bin2hex(random_bytes(32))); // Génération d'un token unique

        // Validation de l'entité
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        // Sauvegarde en base
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'User registered successfully'], Response::HTTP_CREATED);
    }


    /**
 * @OA\Post(
 *     path="/api/security/login",
 *     summary="Connecte un utilisateur",
 *     description="Permet de s'authentifier avec email et mot de passe.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="email", type="string", example="user@example.com"),
 *             @OA\Property(property="password", type="string", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Authentification réussie",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="email", type="string", example="user@example.com"),
 *             @OA\Property(property="roles", type="array", @OA\Items(type="string"), example={"ROLE_USER"}),
 *             @OA\Property(property="apiToken", type="string", example="abcdef123456")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Identifiants invalides",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Invalid credentials")
 *         )
 *     )
 * )
 */
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(
        Request $request,
        UserRepository $userRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Email and password are required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->findOneBy(['email' => $data['email']]);
        if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'credits' => $user->getCredits(),
            'apiToken' => $user->getApiToken(),
        ], Response::HTTP_OK);
    }

    #[Route('/account/me', name: 'account_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'roles' => $user->getRoles(),
            'credits' => $user->getCredits(),
            'apiToken' => $user->getApiToken(),
        ], Response::HTTP_OK);
    }

    #[Route('/account/edit', name: 'account_edit', methods: ['PUT'])]
    public function editAccount(
        Request $request,
        ValidatorInterface $validator
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }
        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }
        if (isset($data['credits'])) {
            $user->setCredits($data['credits']);
        }
        if (isset($data['password'])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        }

        // Validation des changements
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Account updated successfully'], Response::HTTP_OK);
    }
}