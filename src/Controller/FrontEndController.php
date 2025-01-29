<?php

// src/Controller/FrontEndController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontEndController
{
    #[Route('/{path}', name: 'frontend', requirements: ['path' => '.*'])]
    public function index(): Response
    {
        return new Response(file_get_contents(__DIR__ . '/../../public/frontend/index.html'));
    }
}
