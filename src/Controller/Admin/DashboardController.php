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
use MongoDB\BSON\ObjectId;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\StockDashboardService;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly MongoCatalogClient $mongo,
        private readonly StockDashboardService $stockDash,
    ) {}

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Comptages Mongo
        $countProducts   = $this->mongo->products()->countDocuments();
        $countCategories = $this->mongo->categories()->countDocuments();
        $countStocks     = $this->mongo->stocks()->countDocuments();

        // Stock bas
        $threshold = 10;

        $countLowStock = $this->stockDash->countLowStocks($threshold);
        $lowStocks     = $this->stockDash->findLowStocks($threshold, 30);

        return $this->render('admin/dashboard.html.twig', [
            // remplace articles par produits
            'countProducts'   => $countProducts,
            'countCategories' => $countCategories,
            'countStocks'     => $countStocks,
            'threshold'       => $threshold,
            'lowStocks'       => $lowStocks,
            'countLowStock'   => $countLowStock,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('GSM – Backoffice')
            ->setFaviconPath('favicon.ico');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Gestion');
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-user', User::class);
        yield MenuItem::linkToCrud('Clients', 'fa fa-address-book', Client::class);
        yield MenuItem::linkToCrud('Documents', 'fa fa-file-invoice', Document::class);
        yield MenuItem::linkToCrud('Lignes de documents', 'fa fa-list', DocumentLigne::class);
        yield MenuItem::linkToCrud('Logs', 'fa fa-stream', ActionLog::class);

        yield MenuItem::section('Catalogue');
        yield MenuItem::linkToRoute('Catégories', 'fa fa-folder', 'admin_catalog_categories_index');
        yield MenuItem::linkToRoute('Produits catalogue', 'fa fa-boxes', 'admin_catalog_products_index');
        yield MenuItem::linkToRoute('Stocks', 'fa fa-warehouse', 'admin_catalog_stocks_index');
    }
}
