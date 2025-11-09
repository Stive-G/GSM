<?php
namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_ADMIN')
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs');
    }

    public function configureFields(string $pageName): iterable
    {
        yield EmailField::new('email');
        yield ChoiceField::new('roles')
            ->setChoices([
                'Admin'      => 'ROLE_ADMIN',
                'Direction'  => 'ROLE_DIRECTION',
                'Magasinier' => 'ROLE_MAGASINIER',
                'Vendeur'    => 'ROLE_VENDEUR',
            ])
            ->allowMultipleChoices()
            ->renderExpanded(false);
        yield ArrayField::new('roles')->onlyOnIndex()->setLabel('Rôles (vue)');
        yield TextField::new('plainPassword', 'Mot de passe')
            ->onlyOnForms()
            ->setFormTypeOption('mapped', false)
            ->setRequired($pageName === Crud::PAGE_NEW);
    }

    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $plain = $this->getContext()->getRequest()->request->all()['User']['plainPassword'] ?? null;
            if ($plain) $entityInstance->setPassword($this->hasher->hashPassword($entityInstance, $plain));
        }
        parent::persistEntity($em, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $plain = $this->getContext()->getRequest()->request->all()['User']['plainPassword'] ?? null;
            if ($plain) $entityInstance->setPassword($this->hasher->hashPassword($entityInstance, $plain));
        }
        parent::updateEntity($em, $entityInstance);
    }
}
