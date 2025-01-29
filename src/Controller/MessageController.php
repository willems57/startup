<?php

namespace App\Controller;

use App\Message\CustomMessage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class MessageController extends AbstractController
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    #[Route('/api/send-message', methods: ['POST'])]
    public function sendMessage(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['content']) || empty($data['content'])) {
            return new JsonResponse(['error' => 'Content is required'], 400);
        }

        if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'Valid email is required'], 400);
        }

        $message = new CustomMessage($data['content'], $data['email']);
        $this->bus->dispatch($message);

        return new JsonResponse(['status' => 'Message sent to ' . $data['email']], 201);
    }
}