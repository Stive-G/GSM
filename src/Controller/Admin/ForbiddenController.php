<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ForbiddenController extends AbstractController
{
    #[IsGranted('ROLE_VENDEUR')]
    #[Route('/admin/forbidden', name: 'admin_forbidden', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/forbidden.html.twig', [
            'title' => 'Accès refusé',
        ]);
    }
}
