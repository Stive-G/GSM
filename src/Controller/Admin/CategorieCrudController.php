<?php
namespace App\Controller\Admin;

use App\Entity\Categorie;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * LEGACY: SQL-based categories (kept for reference/import only).
 */
class CategorieCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return Categorie::class; }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_DIRECTION')
            ->setEntityLabelInPlural('Catégories')
            ->setEntityLabelInSingular('Catégorie');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Nom');
    }
}
