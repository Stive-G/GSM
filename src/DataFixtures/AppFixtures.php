<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private UserRepository $users
    ) {}

    public function load(ObjectManager $om): void
    {
        $defs = [
            ['admin@gsm.local', 'ROLE_ADMIN', 'admin123'],
            ['dir@gsm.local', 'ROLE_DIRECTION', 'dir123'],
            ['mag@gsm.local', 'ROLE_MAGASINIER', 'mag123'],
            ['ven@gsm.local', 'ROLE_VENDEUR', 'ven123'],
        ];

        foreach ($defs as [$email, $role, $pwd]) {

            if ($this->users->findOneBy(['email' => $email])) {
                continue; // déjà créé
            }

            $u = (new User())
                ->setEmail($email)
                ->setRoles([$role]);

            $u->setPassword($this->hasher->hashPassword($u, $pwd));

            $om->persist($u);
        }

        $om->flush();
    }
}
