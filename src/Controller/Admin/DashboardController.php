<?php

namespace App\Controller\Admin;

use App\Entity\ActionLog;
use App\Entity\Client;
use App\Entity\Document;
use App\Entity\DocumentLigne;
use App\Entity\User;
use App\Infrastructure\Mongo\MongoCatalogClient;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private MongoCatalogClient $mongo,
    ) {}

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Plus d’entité Article SQL
        $countArticles = 0;

        // Stock compté côté Mongo
        $countStocks   = $this->mongo->stocks()->countDocuments();

        // Pour l’instant on ne gère pas encore les alertes de stock bas
        $threshold     = 10;
        $lowStocks     = [];
        $countLowStock = 0;

        return $this->render('admin/dashboard.html.twig', [
            'countArticles' => $countArticles,
            'countStocks'   => $countStocks,
            'threshold'     => $threshold,
            'lowStocks'     => $lowStocks,
            'countLowStock' => $countLowStock,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('GSM – Backoffice');
    }

    public function configureMenuItems(): iterable
    {
        // Dashboard
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        // === PARTIE SQL ===
        yield MenuItem::section('Gestion SQL');

        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-user', User::class);
        yield MenuItem::linkToCrud('Clients', 'fa fa-address-book', Client::class);
        yield MenuItem::linkToCrud('Documents', 'fa fa-file-invoice', Document::class);
        yield MenuItem::linkToCrud('Lignes de documents', 'fa fa-list', DocumentLigne::class);
        yield MenuItem::linkToCrud('Logs', 'fa fa-stream', ActionLog::class);

        // === PARTIE CATALOGUE MONGO ===
        yield MenuItem::section('Catalogue (Mongo)');

        yield MenuItem::linkToRoute(
            'Produits catalogue',
            'fa fa-boxes',
            'admin_catalog_products_index'
        );

        yield MenuItem::linkToRoute(
            'Stocks',
            'fa fa-warehouse',
            'admin_catalog_stocks_index'
        );
    }
}
