<?php

namespace App\Command;

use App\Service\ProductRefSyncService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:sync:product-ref',
    description: 'Synchronise les produits MongoDB vers la table MySQL product_ref'
)]
class SyncProductRefCommand extends Command
{
    public function __construct(private ProductRefSyncService $sync, ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $n = $this->sync->sync();
        $output->writeln(sprintf('OK %d produits synchronisés dans product_ref', $n));
        return Command::SUCCESS;
    }
}
