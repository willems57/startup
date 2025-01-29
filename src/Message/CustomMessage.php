<?php

namespace App\Message;

class CustomMessage
{
    private string $content;
    private string $email;

    public function __construct(string $content, string $email)
    {
        $this->content = $content;
        $this->email = $email;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
