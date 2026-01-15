<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Symfony\Component\Validator\Constraints as Assert;

final class UserCrudController extends AbstractCrudController
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher) {}

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

    private function roleChoices(): array
    {
        return [
            'Admin'      => 'ROLE_ADMIN',
            'Direction'  => 'ROLE_DIRECTION',
            'Magasinier' => 'ROLE_MAGASINIER',
            'Vendeur'    => 'ROLE_VENDEUR',
        ];
    }

    private function normalizeRoles(User $user): void
    {
        $roles = $user->getRoles();

        // On retire ROLE_USER pour ne garder que le "rôle métier"
        $main = array_values(array_diff($roles, ['ROLE_USER']))[0] ?? null;

        $final = ['ROLE_USER'];
        if ($main) {
            $final[] = $main;
        }

        $user->setRoles(array_values(array_unique($final)));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id', 'ID')->onlyOnIndex();
        // Index : email NON cliquable
        yield TextField::new('email', 'Email')
            ->onlyOnIndex();

        // Index : rôle
        yield TextField::new('mainRoleLabel', 'Rôle')
            ->onlyOnIndex();

        // Formulaire
        yield EmailField::new('email')
            ->onlyOnForms();

        yield ChoiceField::new('mainRole', 'Rôle')
            ->setChoices($this->roleChoices())
            ->renderExpanded(false)
            ->onlyOnForms();

        yield TextField::new('plainPassword', 'Mot de passe')
            ->onlyOnForms()
            ->setFormTypeOption('mapped', false)
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->setFormTypeOption('constraints', [
                new Assert\Length(['min' => 8, 'minMessage' => '8 caractères minimum.'])
            ]);
    }

    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $data = $this->getContext()->getRequest()->request->all();
            $plain = $data['User']['plainPassword'] ?? null;

            if (is_string($plain) && trim($plain) !== '') {
                $entityInstance->setPassword(
                    $this->hasher->hashPassword($entityInstance, $plain)
                );
            }

            $this->normalizeRoles($entityInstance);
        }

        parent::persistEntity($em, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $data = $this->getContext()->getRequest()->request->all();
            $plain = $data['User']['plainPassword'] ?? null;

            if (is_string($plain) && trim($plain) !== '') {
                $entityInstance->setPassword(
                    $this->hasher->hashPassword($entityInstance, $plain)
                );
            }

            $this->normalizeRoles($entityInstance);
        }

        parent::updateEntity($em, $entityInstance);
    }
}
