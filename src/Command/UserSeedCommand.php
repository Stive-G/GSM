<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:user:seed',
    description: 'Crée les comptes par défaut si absents'
)]
final class UserSeedCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $defs = [
            ['admin@gsm.local', 'ROLE_ADMIN',      'admin123'],
            ['dir@gsm.local',   'ROLE_DIRECTION',  'dir123'],
            ['mag@gsm.local',   'ROLE_MAGASINIER', 'mag123'],
            ['ven@gsm.local',   'ROLE_VENDEUR',    'ven123'],
        ];

        $repo = $this->em->getRepository(User::class);

        $created = 0;
        foreach ($defs as [$email, $role, $pwd]) {
            if ($repo->findOneBy(['email' => $email])) {
                $output->writeln("= skip $email (exists)");
                continue;
            }

            $u = new User();
            $u->setEmail($email);
            $u->setRoles([$role]);
            $u->setPassword($this->hasher->hashPassword($u, $pwd));

            $this->em->persist($u);
            $created++;

            $output->writeln("+ created $email ($role)");
        }

        $this->em->flush();
        $output->writeln("[seed] done. created=$created");

        return Command::SUCCESS;
    }
}
