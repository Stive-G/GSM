<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Magasin;
use App\Entity\MouvementStock;
use App\Entity\Stock;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(private readonly ManagerRegistry $doctrine) {}

    public function index(): Response
    {
        /** @var EntityManagerInterface $em */
        $em = $this->doctrine->getManager();

        /** @var \App\Repository\ArticleRepository $articleRepo */
        $articleRepo = $em->getRepository(Article::class);
        $countArticles = $articleRepo->count([]);

        /** @var \App\Repository\MagasinRepository $magasinRepo */
        $magasinRepo = $em->getRepository(Magasin::class);
        $countMagasins = $magasinRepo->count([]);

        /** @var \App\Repository\MouvementStockRepository $mvtRepo */
        $mvtRepo = $em->getRepository(MouvementStock::class);
        $countMvts = $mvtRepo->count([]);

        /** @var \App\Repository\StockRepository $stockRepo */
        $stockRepo = $em->getRepository(Stock::class);
        $countStocks = $stockRepo->count([]);

        // 🔔 Alerte stock bas (seuil configuré à 5)
        $threshold = 5;
        $countLowStock = $stockRepo->countLowStock($threshold);
        $lowStocks = $stockRepo->findLowStock($threshold);

        return $this->render('admin/dashboard.html.twig', [
            'countArticles' => $countArticles,
            'countMagasins' => $countMagasins,
            'countMvts'     => $countMvts,
            'countStocks'   => $countStocks,
            'countLowStock' => $countLowStock,
            'lowStocks'     => $lowStocks,
            'threshold'     => $threshold,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('GSM')
            ->renderContentMaximized(); // plein écran pour le tableau de bord
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        // 👑 ADMIN
        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class)
                ->setPermission('ROLE_ADMIN');
            yield MenuItem::linkToRoute(
                'Journaux (Mongo)',
                'fa fa-list-check',
                'admin_logs'
            )->setPermission('ROLE_ADMIN');
        }

        // 📦 MAGASINIER / DIRECTION / ADMIN
        if ($this->isGranted('ROLE_MAGASINIER') || $this->isGranted('ROLE_DIRECTION') || $this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::linkToRoute(
                'Transfert de stock',
                'fa fa-right-left',
                'admin_transfer'
            );
            yield MenuItem::linkToCrud(
                'Historique transferts',
                'fa fa-clock-rotate-left',
                \App\Entity\Transfer::class
            );
        }

        // 🏢 DIRECTION / ADMIN
        if ($this->isGranted('ROLE_DIRECTION') || $this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::linkToCrud('Catégories', 'fa fa-tags', \App\Entity\Categorie::class);
            yield MenuItem::linkToCrud('Articles', 'fa fa-box', Article::class);
            yield MenuItem::linkToCrud('Magasins', 'fa fa-warehouse', Magasin::class);
            yield MenuItem::linkToCrud('Conditionnements', 'fa fa-boxes-stacked', \App\Entity\Conditionnement::class);
            yield MenuItem::linkToCrud('Clients', 'fa fa-user', \App\Entity\Client::class);
            yield MenuItem::linkToCrud('Documents', 'fa fa-file-invoice', \App\Entity\Document::class);
        }

        // 🔄 MOUVEMENTS
        if ($this->isGranted('ROLE_MAGASINIER') || $this->isGranted('ROLE_DIRECTION') || $this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::linkToCrud('Mouvements', 'fa fa-right-left', MouvementStock::class);
        }
    }
}
