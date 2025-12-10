<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ErrorController extends AbstractController
{
    #[Route('/404', name: 'custom_404')]
    public function notFound(): Response
    {
        return $this->render('bundles/TwigBundle/Exception/error404.html.twig', [
            'controller_name' => 'ErrorController',
        ]);
    }
}
