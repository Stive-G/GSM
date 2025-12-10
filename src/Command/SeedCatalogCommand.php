<?php

namespace App\Command;

use App\Service\Catalog\ProductCatalogService;
use App\Service\Stock\StockService;
use MongoDB\BSON\UTCDateTime;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'gsm:catalog:seed',
    description: 'Insère des produits et stocks de démonstration dans MongoDB.'
)]
class SeedCatalogCommand extends Command
{
    public function __construct(
        private ProductCatalogService $catalog,
        private StockService $stock
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Insertion catalogue de test...</info>');

        $cementId = $this->catalog->create([
            'sku'        => 'CIM-25KG',
            'label'      => 'Ciment gris 25kg',
            'categoryId' => 'beton',
            'unit'       => 'sac',
            'attributes' => [
                'poids' => 25,
                'type'  => 'ciment',
            ],
            'prices' => [
                'default' => [
                    'ht'  => 7.90,
                    'ttc' => 9.48,
                ],
            ],
            'createdAt' => new UTCDateTime(),
        ]);

        $output->writeln(sprintf('Produit ciment : %s', $cementId));

        $this->stock->setStock($cementId, 'default', 1, 120);

        $output->writeln('<info>Seed terminé.</info>');

        return Command::SUCCESS;
    }
}
