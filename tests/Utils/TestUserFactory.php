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

        // Si déjà présent, on le réutilise
        $existing = $repo->findOneBy(['email' => 'admin@test.com']);
        if ($existing instanceof User) {
            return $existing;
        }

        $user = new User();
        $user->setEmail('admin@test.com');

        // On lui donne ici tous les rôles nécessaires pour /admin
        $user->setRoles(['ROLE_ADMIN', 'ROLE_VENDEUR']);

        $hashed = $hasher->hashPassword($user, 'admin123');
        $user->setPassword($hashed);

        $em->persist($user);
        $em->flush();

        return $user;
    }

    public static function createUser(EntityManagerInterface $em, UserPasswordHasherInterface $hasher): User
    {
        $repo = $em->getRepository(User::class);

        // Idem : si déjà en base, on réutilise
        $existing = $repo->findOneBy(['email' => 'user@test.com']);
        if ($existing instanceof User) {
            return $existing;
        }

        $user = new User();
        $user->setEmail('user@test.com');
        $user->setRoles(['ROLE_USER']);

        $hashed = $hasher->hashPassword($user, 'user123');
        $user->setPassword($hashed);

        $em->persist($user);
        $em->flush();

        return $user;
    }
}
