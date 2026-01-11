<?php

namespace App\Controller\Admin\Catalog;

use App\Infrastructure\Mongo\MongoCatalogClient;
use App\Service\Catalog\ProductCatalogService;
use MongoDB\BSON\ObjectId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;

#[AdminRoute('/catalog/stocks', name: 'catalog_stocks')]
final class StockAdminController extends AbstractController
{
    public function __construct(
        private readonly MongoCatalogClient $mongo,
        private readonly ProductCatalogService $products,
    ) {}

    #[AdminRoute('/', name: 'index')]
    public function index(Request $request): Response
    {
        $filterProduct = trim((string) $request->query->get('productId', ''));

        $query = [];
        if ($filterProduct !== '') {
            $query['productId'] = $filterProduct;
        }

        $cursor = $this->mongo->stocks()->find($query, [
            'sort' => ['productId' => 1],
        ]);

        $rawStocks = iterator_to_array($cursor, false);

        $stocks = [];
        foreach ($rawStocks as $doc) {
            $arr = $doc instanceof \MongoDB\Model\BSONDocument ? $doc->getArrayCopy() : (array) $doc;

            $id        = (string) ($arr['_id'] ?? '');
            $productId = (string) ($arr['productId'] ?? '');
            $quantity  = (float)  ($arr['quantity'] ?? 0);

            // label produit (optionnel)
            $productLabel = $productId;
            if ($productId !== '') {
                try {
                    $prod = $this->products->findById($productId);
                    if ($prod) {
                        $productLabel = $prod['label'] ?? $productId;
                    }
                } catch (\Throwable $e) {
                }
            }

            $stocks[] = [
                'id'           => $id,
                'productId'    => $productId,
                'productLabel' => $productLabel,
                'quantity'     => $quantity,
            ];
        }

        return $this->render('admin/catalog/stocks/index.html.twig', [
            'stocks'  => $stocks,
            'filters' => [
                'productId' => $filterProduct,
            ],
        ]);
    }

    #[AdminRoute('/new', name: 'new')]
    public function new(Request $request): Response
    {
        // ProductCatalogService->search retourne un tableau avec items
        $result = $this->products->search([], 1, 100);
        $rawProducts = $result['items'] ?? [];

        $products = [];
        foreach ($rawProducts as $doc) {
            $arr = $doc instanceof \MongoDB\Model\BSONDocument ? $doc->getArrayCopy() : (array) $doc;

            $products[] = [
                'id'    => (string) ($arr['_id'] ?? ''),
                'label' => $arr['label'] ?? (string) ($arr['_id'] ?? ''),
                'sku'   => $arr['sku'] ?? null,
            ];
        }

        if ($request->isMethod('POST')) {
            $productId = trim((string) $request->request->get('productId', ''));
            $quantity  = (float) $request->request->get('quantity', 0);

            if ($productId === '') {
                $this->addFlash('danger', 'Le produit est obligatoire.');
            } else {
                // 1 stock par produit => upsert sur productId
                $this->mongo->stocks()->updateOne(
                    ['productId' => $productId],
                    [
                        '$set' => [
                            'productId' => $productId,
                            'quantity'  => $quantity,
                        ],
                    ],
                    ['upsert' => true]
                );

                $this->addFlash('success', 'Stock créé / mis à jour.');
                return $this->redirectToRoute('admin_catalog_stocks_index');
            }
        }

        return $this->render('admin/catalog/stocks/new.html.twig', [
            'products' => $products,
        ]);
    }

    #[AdminRoute('/{id}/edit', name: 'edit')]
    public function edit(string $id, Request $request): Response
    {
        // sécurise l'ObjectId
        try {
            $oid = new ObjectId($id);
        } catch (\Throwable) {
            throw $this->createNotFoundException('ID invalide');
        }

        $doc = $this->mongo->stocks()->findOne(['_id' => $oid]);
        if (!$doc) {
            throw $this->createNotFoundException('Ligne de stock introuvable');
        }

        $arr = $doc instanceof \MongoDB\Model\BSONDocument ? $doc->getArrayCopy() : (array) $doc;

        $productId = (string) ($arr['productId'] ?? '');
        $quantity  = (float)  ($arr['quantity'] ?? 0);

        $productLabel = $productId;
        if ($productId !== '') {
            try {
                $prod = $this->products->findById($productId);
                if ($prod) {
                    $productLabel = $prod['label'] ?? $productId;
                }
            } catch (\Throwable $e) {
            }
        }

        if ($request->isMethod('POST')) {
            $quantity = (float) $request->request->get('quantity', 0);

            $this->mongo->stocks()->updateOne(
                ['_id' => $oid],
                ['$set' => ['quantity' => $quantity]]
            );

            $this->addFlash('success', 'Quantité mise à jour.');
            return $this->redirectToRoute('admin_catalog_stocks_index');
        }

        return $this->render('admin/catalog/stocks/edit.html.twig', [
            'id'           => $id,
            'productId'    => $productId,
            'productLabel' => $productLabel,
            'quantity'     => $quantity,
        ]);
    }

    #[AdminRoute('/{id}/delete', name: 'delete')]
    public function delete(string $id, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete_stock_' . $id, (string) $request->request->get('_token'))) {
            try {
                $oid = new ObjectId($id);
                $this->mongo->stocks()->deleteOne(['_id' => $oid]);
                $this->addFlash('success', 'Ligne de stock supprimée.');
            } catch (\Throwable) {
                $this->addFlash('danger', 'ID invalide.');
            }
        }

        return $this->redirectToRoute('admin_catalog_stocks_index');
    }
}
