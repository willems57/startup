<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\{
    AuthenticationException,
    CustomUserMessageAuthenticationException
};
use Symfony\Component\Security\Http\Authenticator\{
    AbstractAuthenticator,
    Passport\Badge\UserBadge,
    Passport\Passport,
    Passport\SelfValidatingPassport
};

class ApiTokenAuthentificateurAuthenticator extends AbstractAuthenticator
{
    public function __construct(private UserRepository $repository)
    {
    }

    /**
     * Vérifie si l'en-tête `X-AUTH-TOKEN` est présent.
     */
    public function supports(Request $request): ?bool
    {
        return $request->headers->has('X-AUTH-TOKEN');
    }

    /**
     * Authentifie l'utilisateur en validant le token API.
     */
    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get('X-AUTH-TOKEN');

        // Vérifie si le token est fourni
        if (null === $apiToken) {
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        // Cherche l'utilisateur correspondant au token
        $user = $this->repository->findOneBy(['apiToken' => $apiToken]);

        // Gère les cas où le token est invalide
        if (null === $user) {
            throw new CustomUserMessageAuthenticationException('Invalid API token');
        }

        // Vérifie si le token a expiré (si applicable)
        if (method_exists($user, 'getTokenExpiresAt') && $user->getTokenExpiresAt() < new \DateTime()) {
            throw new CustomUserMessageAuthenticationException('API token expired');
        }

        return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier()));
    }

    /**
     * Action à exécuter en cas de succès de l'authentification.
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new JsonResponse([
            'message' => 'Authentication successful',
            'roles' => $token->getUser()->getRoles(),
        ]);
    }

    /**
     * Action à exécuter en cas d'échec de l'authentification.
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(
            ['message' => strtr($exception->getMessageKey(), $exception->getMessageData())],
            Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * (Optionnel) Fournit une réponse si l'utilisateur n'est pas authentifié.
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new JsonResponse(
            ['message' => 'Authentication required'],
            Response::HTTP_UNAUTHORIZED
        );
    }
}
