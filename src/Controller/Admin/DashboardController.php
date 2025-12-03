<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\Catalog\CatalogMagasinRepository;
use App\Repository\Catalog\CatalogProductRepository;
use App\Repository\Catalog\CatalogStockRepository;
use App\Repository\DocumentRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly CatalogProductRepository $productRepository,
        private readonly CatalogMagasinRepository $magasinRepository,
        private readonly CatalogStockRepository $stockRepository,
        private readonly DocumentRepository $documentRepository,
    ) {
    }

    public function index(): Response
    {
        $countArticles = count($this->productRepository->findAll());
        $countMagasins = count($this->magasinRepository->findAll());
        $countStocks = count($this->stockRepository->findAll());

        return $this->render('admin/dashboard.html.twig', [
            'countArticles' => $countArticles,
            'countMagasins' => $countMagasins,
            'countMvts'     => $this->documentRepository->count([]),
            'countStocks'   => $countStocks,
            'countLowStock' => 0,
            'lowStocks'     => [],
            'threshold'     => 0,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('GSM')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class)
                ->setPermission('ROLE_ADMIN');
            yield MenuItem::linkToRoute('Journaux (SQL)', 'fa fa-list-check', 'admin_logs')
                ->setPermission('ROLE_ADMIN');
        }

        yield MenuItem::section('Catalogue (MongoDB)');
        if ($this->isGranted('ROLE_DIRECTION') || $this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::linkToRoute('Catégories', 'fa fa-tags', 'admin_catalog_categories_index');
            yield MenuItem::linkToRoute('Produits', 'fa fa-box', 'admin_catalog_products_index');
            yield MenuItem::linkToRoute('Magasins', 'fa fa-warehouse', 'admin_catalog_magasins_index');
        }

        yield MenuItem::section('Opérations');
        if ($this->isGranted('ROLE_MAGASINIER') || $this->isGranted('ROLE_DIRECTION') || $this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::linkToRoute('Transfert de stock', 'fa fa-right-left', 'admin_transfer');
            yield MenuItem::linkToCrud('Historique transferts', 'fa fa-clock-rotate-left', \App\Entity\Transfer::class);
            yield MenuItem::linkToCrud('Mouvements', 'fa fa-right-left', \App\Entity\MouvementStock::class);
        }

        if ($this->isGranted('ROLE_DIRECTION') || $this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::linkToCrud('Clients', 'fa fa-user', \App\Entity\Client::class);
            yield MenuItem::linkToCrud('Documents', 'fa fa-file-invoice', \App\Entity\Document::class);
        }
    }
}
