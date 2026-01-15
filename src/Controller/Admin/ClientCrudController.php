<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Validator\Constraints as Assert;

class ClientCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Client::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setEntityPermission('ROLE_VENDEUR')
            ->setEntityLabelInPlural('Clients')->setEntityLabelInSingular('Client');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Nom')
            ->setFormTypeOption('constraints', [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 180]),
            ]);

        yield TextField::new('phone', 'Téléphone')
            ->setRequired(false)
            ->setFormTypeOption('constraints', [
                new Assert\Regex([
                    'pattern' => '/^\+?\d{8,15}$/',
                    'message' => 'Téléphone invalide (8-15 chiffres, + optionnel).'
                ])
            ]);

        yield EmailField::new('email')
            ->setRequired(false)
            ->setFormTypeOption('constraints', [
                new Assert\Email(['message' => 'Email invalide.']),
                new Assert\Length(['max' => 190]),
            ]);
    }
}
