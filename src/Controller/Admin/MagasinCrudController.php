<?php
namespace App\Controller\Admin;

use App\Entity\Magasin;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * LEGACY: SQL-based magasins (kept for reference/import only).
 */
class MagasinCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Magasin::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_DIRECTION')
            ->setEntityLabelInPlural('Magasins')
            ->setEntityLabelInSingular('Magasin');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('code', 'Code');
        yield TextField::new('name', 'Nom');
    }
}
