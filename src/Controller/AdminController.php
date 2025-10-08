<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/admin/orders', name: 'admin_orders')]
    public function orders(): Response
    {
        return $this->render('admin/orders.html.twig');
    }

    #[Route('/admin/materials', name: 'admin_materials')]
    public function materials(): Response
    {
        return $this->render('admin/materials.html.twig');
    }

    #[Route('/admin/finishes', name: 'admin_finishes')]
    public function finishes(): Response
    {
        return $this->render('admin/finishes.html.twig');
    }

    #[Route('/admin/colors', name: 'admin_colors')]
    public function colors(): Response
    {
        return $this->render('admin/colors.html.twig');
    }

    #[Route('/admin/settings', name: 'admin_settings')]
    public function settings(): Response
    {
        return $this->render('admin/settings.html.twig');
    }
}
