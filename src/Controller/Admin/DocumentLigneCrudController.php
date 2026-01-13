<?php

namespace App\Controller\Admin;

use App\Entity\DocumentLigne;
use App\Service\ProductRefSyncService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DocumentLigneCrudController extends AbstractCrudController
{
    public function __construct(
        private ProductRefSyncService $sync,
        private AdminUrlGenerator $adminUrlGenerator
    ) {}

    public static function getEntityFqcn(): string
    {
        return DocumentLigne::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_VENDEUR')
            ->setEntityLabelInSingular('Ligne de document')
            ->setEntityLabelInPlural('Lignes de documents');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('productLabel', 'Produit');
        yield TextField::new('unit', 'Unité');
        yield TextField::new('unitPriceHt', 'Prix unitaire HT');
        yield TextField::new('quantity', 'Quantité');
    }

    public function configureActions(Actions $actions): Actions
    {
        $syncAction = Action::new('syncProducts', 'Sync produits', 'fa fa-rotate')
            ->linkToCrudAction('syncProducts')
            ->createAsGlobalAction();

        return $actions
            ->add(Crud::PAGE_INDEX, $syncAction);
    }

    public function syncProducts(): RedirectResponse
    {
        $n = $this->sync->sync();
        $this->addFlash('success', sprintf('Sync OK: %d produits dans product_ref', $n));

        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Crud::PAGE_INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }
}
