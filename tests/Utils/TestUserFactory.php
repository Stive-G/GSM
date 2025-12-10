<?php

namespace App\Tests\Utils;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestUserFactory
{
    public static function createAdmin(EntityManagerInterface $em, UserPasswordHasherInterface $hasher): User
    {
        $repo = $em->getRepository(User::class);

        // Si l'admin existe déjà, on le réutilise
        $user = $repo->findOneBy(['email' => 'admin@test.com']);
        if ($user) {
            return $user;
        }

        $user = new User();
        $user->setEmail('admin@test.com');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($hasher->hashPassword($user, 'admin123'));

        $em->persist($user);
        $em->flush();

        return $user;
    }

    public static function createUser(EntityManagerInterface $em, UserPasswordHasherInterface $hasher): User
    {
        $repo = $em->getRepository(User::class);

        $user = $repo->findOneBy(['email' => 'user@test.com']);
        if ($user) {
            return $user;
        }

        $user = new User();
        $user->setEmail('user@test.com');
        // Pas de rôle particulier ici => pas accès à /admin
        $user->setRoles([]); // ou ['ROLE_USER'] selon ton appli
        $user->setPassword($hasher->hashPassword($user, 'user123'));

        $em->persist($user);
        $em->flush();

        return $user;
    }

    public static function createVendeur(EntityManagerInterface $em, UserPasswordHasherInterface $hasher): User
    {
        $repo = $em->getRepository(User::class);

        $user = $repo->findOneBy(['email' => 'vendeur@test.com']);
        if ($user) {
            return $user;
        }

        $user = new User();
        $user->setEmail('vendeur@test.com');
        $user->setRoles(['ROLE_VENDEUR']);
        $user->setPassword($hasher->hashPassword($user, 'vendeur123'));

        $em->persist($user);
        $em->flush();

        return $user;
    }
}
