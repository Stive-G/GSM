<?php

namespace App\Controller\Admin\Catalog;

use App\Repository\Catalog\CatalogCategoryRepository;
use App\Service\Catalog\ProductCatalogService;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use MongoDB\BSON\UTCDateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AdminRoute('/catalog/products', name: 'catalog_products')]
class ProductAdminController extends AbstractController
{
    public function __construct(
        private ProductCatalogService $catalog,
        private CatalogCategoryRepository $categories
    ) {}

    #[AdminRoute('/', name: 'index')]
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $filters = [
            'text'       => $request->query->get('q'),
            'categoryId' => $request->query->get('categoryId'),
        ];

        // Résultats bruts
        $raw = $this->catalog->search($filters, $page, 50);

        // Normalisation pour Twig
        $products = [];
        foreach ($raw as $doc) {
            $arr = $doc instanceof \MongoDB\Model\BSONDocument ? $doc->getArrayCopy() : (array) $doc;
            $arr['id'] = (string) $arr['_id'];
            $products[] = $arr;
        }

        $categories = $this->categories->findAllActive();

        return $this->render('admin/catalog/products/index.html.twig', [
            'products'   => $products,
            'filters'    => $filters,
            'page'       => $page,
            'categories' => $categories,
        ]);
    }

    #[AdminRoute('/new', name: 'new')]
    public function new(Request $request): Response
    {
        $categories = $this->categories->findAllActive();

        if ($request->isMethod('POST')) {
            $attributes = $this->buildAttributesFromRequest($request);

            $details = [
                'description' => $request->request->get('description'),
            ];

            $data = [
                'sku'        => $request->request->get('sku'),
                'label'      => $request->request->get('label'),
                'categoryId' => $request->request->get('categoryId') ?: null,
                'unit'       => $request->request->get('unit'),
                'prices'     => [
                    'default' => [
                        'ht'  => (float) $request->request->get('price_ht'),
                        'ttc' => (float) $request->request->get('price_ttc'),
                    ],
                ],
                'details'    => $details,
                'attributes' => $attributes,
                'images'     => array_filter(array_map('trim', explode("\n", (string) $request->request->get('images')))),
                'createdAt'  => new UTCDateTime(),
                'updatedAt'  => new UTCDateTime(),
            ];

            $id = $this->catalog->create($data);
            $this->addFlash('success', 'Produit créé (' . $id . ').');

            return $this->redirectToRoute('admin_catalog_products_index');
        }

        return $this->render('admin/catalog/products/new.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[AdminRoute('/{id}/edit', name: 'edit')]
    public function edit(string $id, Request $request): Response
    {
        $product = $this->catalog->findById($id);
        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable');
        }
        $product['id'] = (string) $product['_id'];

        $categories = $this->categories->findAllActive();

        if ($request->isMethod('POST')) {
            $product['sku']        = $request->request->get('sku');
            $product['label']      = $request->request->get('label');
            $product['categoryId'] = $request->request->get('categoryId') ?: null;
            $product['unit']       = $request->request->get('unit');

            $product['prices']['default']['ht']  = (float) $request->request->get('price_ht');
            $product['prices']['default']['ttc'] = (float) $request->request->get('price_ttc');

            $product['details']['description'] = $request->request->get('description');

            // ⬇️ ICI : nouvelles caractéristiques
            $product['attributes'] = $this->buildAttributesFromRequest($request);

            $product['images'] = array_filter(array_map('trim', explode("\n", (string) $request->request->get('images'))));

            $product['updatedAt'] = new UTCDateTime();

            $this->catalog->update($id, $product);
            $this->addFlash('success', 'Produit mis à jour.');

            return $this->redirectToRoute('admin_catalog_products_index');
        }

        // Préparation des champs pour le formulaire
        $priceHt       = $product['prices']['default']['ht']  ?? 0;
        $priceTtc      = $product['prices']['default']['ttc'] ?? 0;
        $description   = $product['details']['description'] ?? '';
        $attributes    = $product['attributes'] ?? [];
        $imagesText    = isset($product['images']) ? implode("\n", (array) $product['images']) : '';

        return $this->render('admin/catalog/products/edit.html.twig', [
            'product'     => $product,
            'id'          => $id,
            'categories'  => $categories,
            'price_ht'    => $priceHt,
            'price_ttc'   => $priceTtc,
            'description' => $description,
            'attributes'  => $attributes,
            'images_text' => $imagesText,
        ]);
    }

    #[AdminRoute('/{id}/delete', name: 'delete')]
    public function delete(string $id, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete_product_' . $id, (string) $request->request->get('_token'))) {
            $this->catalog->delete($id);
            $this->addFlash('success', 'Produit supprimé.');
        }

        return $this->redirectToRoute('admin_catalog_products_index');
    }

    private function buildAttributesFromRequest(Request $request): array
    {
        $keys   = (array) $request->request->all('attribute_keys');
        $values = (array) $request->request->all('attribute_values');

        $attributes = [];

        foreach ($keys as $i => $key) {
            $key = trim((string) $key);
            if ($key === '') {
                continue;
            }

            $value = $values[$i] ?? null;
            if ($value === null || $value === '') {
                continue;
            }

            $value = trim((string) $value);

            // Si c'est numérique, on cast (int/float) pour avoir des vrais nombres en Mongo
            if (is_numeric($value)) {
                $value = $value + 0;
            }

            $attributes[$key] = $value;
        }

        return $attributes;
    }
}
