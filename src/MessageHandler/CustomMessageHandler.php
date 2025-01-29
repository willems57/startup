<?php

namespace App\MessageHandler;

use App\Message\CustomMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CustomMessageHandler implements MessageHandlerInterface
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(CustomMessage $message)
    {
        $email = (new Email())
            ->from('no-reply@example.com') // Adresse de l'expéditeur
            ->to($message->getEmail())    // Adresse du destinataire
            ->subject('Notification')
            ->text($message->getContent());

        $this->mailer->send($email);
    }
}
