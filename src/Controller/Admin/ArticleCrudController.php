<?php
namespace App\Controller\Admin;

use App\Entity\Article;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class ArticleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_DIRECTION')
            ->setEntityLabelInPlural('Articles')
            ->setEntityLabelInSingular('Article');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('reference', 'Référence');
        yield TextField::new('label', 'Libellé');
        yield MoneyField::new('price', 'Prix')->setCurrency('EUR')->setStoredAsCents(false);
        yield BooleanField::new('active', 'Actif');
        yield AssociationField::new('categorie', 'Catégorie');

    }
}
