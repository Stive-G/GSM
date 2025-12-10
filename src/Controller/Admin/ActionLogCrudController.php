<?php

namespace App\Controller\Admin;

use App\Entity\ActionLog;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ActionLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ActionLog::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Log')
            ->setEntityLabelInPlural('Historique des actions')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->showEntityActionsInlined()
            ->setPaginatorPageSize(50)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $date = DateTimeField::new('createdAt', 'Date');

        // Ici on cible la propriété virtuelle "userLabel"
        $user = TextField::new('userLabel', 'Utilisateur')
            ->onlyOnIndex(); // ou ->onlyOnDetail() selon ce que tu veux

        $action = TextField::new('type', 'Action')
            ->formatValue(function ($value) {
                return match ($value) {
                    'route'             => 'Navigation',
                    'entity_created'    => 'Création d\'un enregistrement',
                    'entity_updated'    => 'Modification d\'un enregistrement',
                    'entity_deleted'    => 'Suppression d\'un enregistrement',
                    default             => ucfirst((string) $value),
                };
            });

        $target = TextField::new('route', 'Cible')
            ->formatValue(function ($value) {
                if (!$value) {
                    return '-';
                }

                if (str_contains($value, '\\')) {
                    $parts = explode('\\', $value);
                    return end($parts);
                }

                return $value;
            });

        return [$date, $user, $action, $target];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE);
    }
}
