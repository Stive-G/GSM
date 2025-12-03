<?php
namespace App\Controller\Admin;

use App\Entity\Conditionnement;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * LEGACY: SQL-based packaging (kept for reference/import only).
 */
class ConditionnementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return Conditionnement::class; }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_DIRECTION')
            ->setEntityLabelInPlural('Conditionnements')
            ->setEntityLabelInSingular('Conditionnement');
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('article');
        yield TextField::new('label', 'Libellé');
        yield TextField::new('unit', 'Unité')->setHelp('Ex: pcs, kg, m, m2...');
        yield MoneyField::new('defaultUnitPrice', 'Prix unitaire défaut')->setCurrency('EUR')->setStoredAsCents(false);
    }
}
