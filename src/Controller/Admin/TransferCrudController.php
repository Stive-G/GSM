<?php
namespace App\Controller\Admin;

use App\Entity\Transfer;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class TransferCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return Transfer::class; }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_MAGASINIER')
            ->setEntityLabelInPlural('Transferts')
            ->setEntityLabelInSingular('Transfert')
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        // Lecture seule : désactiver NEW/EDIT/DELETE
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        yield DateTimeField::new('createdAt', 'Date');
        yield AssociationField::new('article');
        yield AssociationField::new('conditionnement');
        yield AssociationField::new('source');
        yield AssociationField::new('destination');
        yield NumberField::new('quantity', 'Quantité')->setNumDecimals(4);
        yield TextareaField::new('comment', 'Commentaire')->hideOnIndex();
        // Mouvements liés (optionnel, seulement en détail)
        if ($pageName === Crud::PAGE_DETAIL) {
            yield AssociationField::new('outMovement', 'Mvt OUT');
            yield AssociationField::new('inMovement', 'Mvt IN');
        }
    }
}
