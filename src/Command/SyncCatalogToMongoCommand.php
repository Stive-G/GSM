<?php

namespace App\Command;

use App\Entity\Article;
use App\Entity\Categorie;
use App\Entity\Conditionnement;
use App\Entity\Magasin;
use App\Entity\Stock;
use App\Repository\Catalog\CatalogCategoryRepository;
use App\Repository\Catalog\CatalogMagasinRepository;
use App\Repository\Catalog\CatalogProductRepository;
use App\Repository\Catalog\CatalogStockRepository;
use Doctrine\ORM\EntityManagerInterface;
use MongoDB\BSON\UTCDateTime;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsCommand(name: 'gsm:sync:catalog-to-mongo', description: 'Import initial du catalogue SQL vers MongoDB (Mongo = vérité)')]
class SyncCatalogToMongoCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CatalogCategoryRepository $categoryRepository,
        private readonly CatalogProductRepository $productRepository,
        private readonly CatalogStockRepository $stockRepository,
        private readonly CatalogMagasinRepository $magasinRepository,
        private readonly SluggerInterface $slugger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Synchronisation du catalogue vers MongoDB');

        $this->syncMagasins($io);
        $this->syncCategories($io);
        $this->syncProducts($io);
        $this->syncStocks($io);

        $io->success('Synchronisation terminée.');
        return Command::SUCCESS;
    }

    private function syncCategories(SymfonyStyle $io): void
    {
        $categories = $this->em->getRepository(Categorie::class)->findAll();
        foreach ($categories as $category) {
            $doc = $this->buildCategoryDocument($category);
            $this->categoryRepository->upsert($doc);
        }
        $io->writeln(sprintf('Catégories synchronisées : %d', count($categories)));
    }

    private function syncProducts(SymfonyStyle $io): void
    {
        $articles = $this->em->getRepository(Article::class)->findAll();
        foreach ($articles as $article) {
            $doc = $this->buildProductDocument($article);
            $this->productRepository->upsert($doc);
        }
        $io->writeln(sprintf('Produits synchronisés : %d', count($articles)));
    }

    private function syncStocks(SymfonyStyle $io): void
    {
        $stocks = $this->em->getRepository(Stock::class)->findAll();
        foreach ($stocks as $stock) {
            $doc = $this->buildStockDocument($stock);
            $this->stockRepository->upsert($doc);
        }
        $io->writeln(sprintf('Stocks synchronisés : %d', count($stocks)));
    }

    private function syncMagasins(SymfonyStyle $io): void
    {
        $magasins = $this->em->getRepository(Magasin::class)->findAll();
        foreach ($magasins as $magasin) {
            $doc = $this->buildMagasinDocument($magasin);
            $this->magasinRepository->upsert($doc);
        }
        $io->writeln(sprintf('Magasins synchronisés : %d', count($magasins)));
    }

    private function buildCategoryDocument(Categorie $category): array
    {
        $slug = strtolower($this->slugger->slug($category->getName()));
        $id = sprintf('cat-%s', $category->getId() ?? $slug);

        return [
            '_id' => $id,
            'name' => $category->getName(),
            'slug' => $slug,
            'parentId' => null,
            'path' => [$id],
            'level' => 1,
            'position' => $category->getId() ?? 0,
            'isActive' => true,
            'imageUrl' => null,
            'filters' => [
                'attributes' => [],
                'brands' => true,
                'price' => true,
            ],
        ];
    }

    private function buildProductDocument(Article $article): array
    {
        $slug = strtolower($this->slugger->slug($article->getLabel()));
        $mainCategory = $article->getCategorie();
        $categoryId = $mainCategory ? sprintf('cat-%s', $mainCategory->getId() ?? strtolower($this->slugger->slug($mainCategory->getName()))) : null;
        $now = new UTCDateTime((int) (microtime(true) * 1000));

        return [
            '_id' => sprintf('prd-%s', $article->getReference()),
            'sku' => $article->getReference(),
            'reference' => $article->getReference(),
            'name' => $article->getLabel(),
            'slug' => $slug,
            'description' => '',
            'categories' => $categoryId ? [$categoryId] : [],
            'mainCategory' => $categoryId,
            'brand' => null,
            'images' => [],
            'attributes' => [],
            'variants' => $this->buildVariants($article),
            'tags' => [],
            'isActive' => $article->isActive(),
            'createdAt' => $now,
            'updatedAt' => $now,
        ];
    }

    private function buildVariants(Article $article): array
    {
        $variants = [];
        /** @var Conditionnement $conditionnement */
        foreach ($article->getConditionnements() as $conditionnement) {
            $variants[] = [
                'variantId' => sprintf('var-%s', $conditionnement->getId() ?? uniqid()),
                'label' => $conditionnement->getLabel(),
                'barcode' => null,
                'unit' => $conditionnement->getUnit(),
                'unitSize' => null,
                'unitSizeUnit' => null,
                'price' => [
                    'ht' => (float) $conditionnement->getDefaultUnitPrice(),
                    'ttc' => (float) $conditionnement->getDefaultUnitPrice(),
                    'currency' => 'EUR',
                    'pricePerBaseUnit' => (float) $conditionnement->getDefaultUnitPrice(),
                ],
                'isActive' => true,
            ];
        }

        if (!$variants) {
            $variants[] = [
                'variantId' => 'var-default',
                'label' => 'Standard',
                'barcode' => null,
                'unit' => null,
                'unitSize' => null,
                'unitSizeUnit' => null,
                'price' => [
                    'ht' => (float) $article->getPrice(),
                    'ttc' => (float) $article->getPrice(),
                    'currency' => 'EUR',
                    'pricePerBaseUnit' => (float) $article->getPrice(),
                ],
                'isActive' => true,
            ];
        }

        return $variants;
    }

    private function buildStockDocument(Stock $stock): array
    {
        $article = $stock->getArticle();
        $conditionnement = $stock->getConditionnement();
        $magasin = $stock->getMagasin();

        return [
            '_id' => sprintf('stock-%s-%s-%s', $article->getId(), $conditionnement->getId(), $magasin->getId()),
            'productId' => sprintf('prd-%s', $article->getReference()),
            'variantId' => sprintf('var-%s', $conditionnement->getId()),
            'magasinId' => sprintf('mag-%s', $magasin->getId()),
            'quantity' => (float) $stock->getQuantity(),
            'securityStock' => 0,
            'updatedAt' => new UTCDateTime(),
        ];
    }

    private function buildMagasinDocument(Magasin $magasin): array
    {
        return [
            '_id' => sprintf('mag-%s', $magasin->getId()),
            'code' => $magasin->getCode(),
            'name' => $magasin->getName(),
            'address' => [
                'street' => null,
                'city' => null,
                'zip' => null,
                'country' => null,
            ],
            'isActive' => true,
        ];
    }
}
