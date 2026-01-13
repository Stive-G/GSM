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
use Symfony\Component\Security\Http\Attribute\IsGranted;


class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly MongoCatalogClient $mongo,
        private readonly StockDashboardService $stockDash,
    ) {}

    #[IsGranted('ROLE_VENDEUR')]
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $threshold = 10;

        $data = [
            'threshold' => $threshold,
            'countProducts' => null,
            'countCategories' => null,
            'countStocks' => null,
            'lowStocks' => [],
            'countLowStock' => 0,
        ];

        // Stocks visibles pour magasinier et +
        if ($this->isGranted('ROLE_MAGASINIER')) {
            $data['countProducts']   = $this->mongo->products()->countDocuments();
            $data['countCategories'] = $this->mongo->categories()->countDocuments();
            $data['countStocks']     = $this->mongo->stocks()->countDocuments();

            $data['countLowStock'] = $this->stockDash->countLowStocks($threshold);
            $data['lowStocks']     = $this->stockDash->findLowStocks($threshold, 30);
        }

        return $this->render('admin/dashboard.html.twig', $data);
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

        // Users = ADMIN only
        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-user', User::class);
            yield MenuItem::linkToCrud('Logs', 'fa fa-stream', ActionLog::class);
        }

        // Clients = vendeur et +
        if ($this->isGranted('ROLE_VENDEUR')) {
            yield MenuItem::linkToCrud('Clients', 'fa fa-address-book', Client::class);
            yield MenuItem::linkToCrud('Documents', 'fa fa-file-invoice', Document::class);
            yield MenuItem::linkToCrud('Lignes de documents', 'fa fa-list', DocumentLigne::class);
        }

        yield MenuItem::section('Catalogue');

        // Catalogue = magasinier et +
        if ($this->isGranted('ROLE_MAGASINIER')) {
            yield MenuItem::linkToRoute('Stocks', 'fa fa-warehouse', 'admin_catalog_stocks_index');
        }

        // Produits + catégories = direction et +
        if ($this->isGranted('ROLE_DIRECTION')) {
            yield MenuItem::linkToRoute('Catégories', 'fa fa-folder', 'admin_catalog_categories_index');
            yield MenuItem::linkToRoute('Produits catalogue', 'fa fa-boxes', 'admin_catalog_products_index');
        }
    }
}
