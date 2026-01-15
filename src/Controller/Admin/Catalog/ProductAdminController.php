<?php

namespace App\Controller\Admin\Catalog;

use App\Service\Catalog\CategoryCatalogService;
use App\Service\Catalog\ProductCatalogService;
use App\Service\Media\ProductImageStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_DIRECTION')]
#[AdminRoute('/catalog/products', name: 'catalog_products')]
final class ProductAdminController extends AbstractController
{
    public function __construct(
        private readonly ProductCatalogService $products,
        private readonly CategoryCatalogService $categories,
        private readonly ProductImageStorage $images,
    ) {}

    #[AdminRoute('/', name: 'index')]
    public function index(Request $request): Response
    {
        $filters = [
            'text'       => $request->query->get('q'),
            'categoryId' => $request->query->get('categoryId'),
        ];

        $result = $this->products->search($filters, 1, 200);

        $cats = $this->categories->findAll();
        $categoryMap = [];
        foreach ($cats as $c) {
            $cid = (string)($c['id'] ?? $c['_id'] ?? '');
            $categoryMap[$cid] = (string)($c['name'] ?? $c['label'] ?? $cid);
        }
        $currency = (string) $this->getParameter('app.currency');
        $currencyLabel = (string) $this->getParameter('app.currency_label');


        return $this->render('admin/catalog/products/index.html.twig', [
            'products'     => $result['items'] ?? $result,
            'categories'   => $cats,
            'categoryMap'  => $categoryMap,
            'filters'      => $filters,
            'currency' => $currency,
            'currencyLabel' => $currencyLabel,
        ]);
    }

    #[AdminRoute('/new', name: 'new')]
    public function new(Request $request): Response
    {
        $categories = $this->categories->findAll();

        if ($request->isMethod('POST')) {
            $sku   = strtoupper(trim((string) $request->request->get('sku')));
            $label = trim((string) $request->request->get('label'));

            if (!$this->isCsrfTokenValid('catalog_product_new', (string)$request->request->get('_token'))) {
                throw $this->createAccessDeniedException('CSRF invalide.');
            }

            if ($sku === '' || $label === '') {
                $this->addFlash('danger', 'SKU et Libellé sont obligatoires.');
                return $this->render('admin/catalog/products/new.html.twig', [
                    'categories' => $categories,
                ]);
            }

            if ($this->products->findBySku($sku)) {
                $this->addFlash('danger', 'Ce SKU existe déjà.');
                return $this->render('admin/catalog/products/new.html.twig', [
                    'categories' => $categories,
                ]);
            }

            if (!preg_match('/^[A-Z0-9_-]{3,40}$/', $sku)) {
                $this->addFlash('danger', 'SKU invalide (3-40, lettres/chiffres, - ou _).');
                return $this->render('admin/catalog/products/new.html.twig', ['categories' => $categories]);
            }

            if (mb_strlen($label) > 160) {
                $this->addFlash('danger', 'Libellé trop long (160 max).');
                return $this->render('admin/catalog/products/new.html.twig', ['categories' => $categories]);
            }

            $unit = trim((string)$request->request->get('unit', ''));
            if ($unit !== '' && !preg_match('/^[\pL0-9\s\.\-\/]{1,20}$/u', $unit)) {
                $this->addFlash('danger', 'Unité invalide.');
                return $this->render('admin/catalog/products/new.html.twig', ['categories' => $categories]);
            }

            $priceHt  = (float)($request->request->get('price_ht') ?? 0);
            $priceTtc = (float)($request->request->get('price_ttc') ?? 0);

            if ($priceHt < 0 || $priceTtc < 0 || $priceHt > 10000000 || $priceTtc > 10000000) {
                $this->addFlash('danger', 'Prix invalide.');
                return $this->render('admin/catalog/products/new.html.twig', ['categories' => $categories]);
            }

            $categoryId = $request->request->get('categoryId') ?: null;
            if ($categoryId !== null) {
                try {
                    new \MongoDB\BSON\ObjectId((string)$categoryId);
                } catch (\Throwable) {
                    $categoryId = null;
                }
            }

            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile[] $uploaded */
            $uploaded = $request->files->get('images', []);
            $uploaded = is_array($uploaded) ? $uploaded : [];

            $attemptedUpload = count(array_filter($uploaded)) > 0;

            $uploadResult = $this->images->storeProductImages($sku, $uploaded);
            $imagePaths = $uploadResult['paths'] ?? [];
            $uploadErrors = $uploadResult['errors'] ?? [];

            foreach ($uploadErrors as $msg) {
                $this->addFlash('warning', $msg);
            }

            // Si user a tenté un upload mais 0 image acceptée => on bloque la création
            if ($attemptedUpload && count($imagePaths) === 0) {
                $this->addFlash('danger', "Aucune image n'a été acceptée (format/poids invalide).");
                return $this->render('admin/catalog/products/new.html.twig', [
                    'categories' => $categories,
                ]);
            }

            $attributes = $this->buildAttributes(
                $request->request->all('attribute_keys'),
                $request->request->all('attribute_values')
            );

            $now = new \MongoDB\BSON\UTCDateTime();

            $data = [
                'sku'         => $sku,
                'label'       => $label,
                'slug'        => $this->slugify($label),
                'categoryId'  => $categoryId,
                'unit'        => $unit !== '' ? $unit : null,
                'price_ht'    => $priceHt,
                'price_ttc'   => $priceTtc,
                'description' => $request->request->get('description') ?: null,
                'attributes'  => $attributes,
                'images'      => $imagePaths,
                'createdAt'   => $now,
                'updatedAt'   => $now,
            ];

            $this->products->create($data);

            if ($uploadErrors) {
                $this->addFlash('warning', 'Produit créé, mais certaines images ont été refusées.');
            } else {
                $this->addFlash('success', 'Produit créé.');
            }

            return $this->redirectToRoute('admin_catalog_products_index');
        }

        return $this->render('admin/catalog/products/new.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[AdminRoute('/cards', name: 'cards')]
    public function cards(Request $request): Response
    {
        $filters = [
            'text'       => $request->query->get('q'),
            'categoryId' => $request->query->get('categoryId'),
        ];

        $result = $this->products->search($filters, 1, 100);

        $cats = $this->categories->findAll();
        $categoryMap = [];
        foreach ($cats as $c) {
            $cid = (string)($c['id'] ?? $c['_id'] ?? '');
            $categoryMap[$cid] = (string)($c['name'] ?? $c['label'] ?? $cid);
        }

        return $this->render('admin/catalog/products/_cards.html.twig', [
            'products'    => $result['items'] ?? [],
            'categoryMap' => $categoryMap,
        ]);
    }

    #[AdminRoute('/{id}/edit', name: 'edit')]
    public function edit(string $id, Request $request): Response
    {
        $product = $this->products->findById($id);
        if (!$product) {
            throw $this->createNotFoundException();
        }

        $categories = $this->categories->findAll();

        if ($request->isMethod('POST')) {

            if (!$this->isCsrfTokenValid('catalog_product_edit_' . $id, (string)$request->request->get('_token'))) {
                throw $this->createAccessDeniedException('CSRF invalide.');
            }

            // --- suppression d'une image (sans supprimer le fichier disque) ---
            $removeOne = trim((string) $request->request->get('remove_one', ''));
            if ($removeOne !== '') {
                $existing = array_values(array_filter((array)($product['images'] ?? [])));
                $newList  = array_values(array_diff($existing, [$removeOne]));

                $this->products->update($id, [
                    'images'    => $newList,
                    'updatedAt' => new \MongoDB\BSON\UTCDateTime(),
                ]);

                $this->addFlash('success', 'Image supprimée.');
                return $this->redirectToRoute('admin_catalog_products_edit', ['id' => $id]);
            }

            $sku   = strtoupper(trim((string) $request->request->get('sku')));
            $label = trim((string) $request->request->get('label'));

            if ($sku === '' || $label === '') {
                $this->addFlash('danger', 'SKU et Libellé sont obligatoires.');
                return $this->redirectToRoute('admin_catalog_products_edit', ['id' => $id]);
            }

            if (!preg_match('/^[A-Z0-9_-]{3,40}$/', $sku)) {
                $this->addFlash('danger', 'SKU invalide (3-40, lettres/chiffres, - ou _).');
                return $this->redirectToRoute('admin_catalog_products_edit', ['id' => $id]);
            }

            if (mb_strlen($label) > 160) {
                $this->addFlash('danger', 'Libellé trop long (160 max).');
                return $this->redirectToRoute('admin_catalog_products_edit', ['id' => $id]);
            }

            $unit = trim((string)$request->request->get('unit', ''));
            if ($unit !== '' && !preg_match('/^[\pL0-9\s\.\-\/]{1,20}$/u', $unit)) {
                $this->addFlash('danger', 'Unité invalide.');
                return $this->redirectToRoute('admin_catalog_products_edit', ['id' => $id]);
            }

            $priceHt  = (float)($request->request->get('price_ht') ?? 0);
            $priceTtc = (float)($request->request->get('price_ttc') ?? 0);

            if ($priceHt < 0 || $priceTtc < 0 || $priceHt > 10000000 || $priceTtc > 10000000) {
                $this->addFlash('danger', 'Prix invalide.');
                return $this->redirectToRoute('admin_catalog_products_edit', ['id' => $id]);
            }

            $categoryId = $request->request->get('categoryId') ?: null;
            if ($categoryId !== null) {
                try {
                    new \MongoDB\BSON\ObjectId((string)$categoryId);
                } catch (\Throwable) {
                    $categoryId = null;
                }
            }

            $existing = array_values(array_filter((array)($product['images'] ?? [])));

            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile[] $uploaded */
            $uploaded = $request->files->get('images', []);
            $uploaded = is_array($uploaded) ? $uploaded : [];

            $attemptedUpload = count(array_filter($uploaded)) > 0;

            $uploadResult = $this->images->storeProductImages($sku, $uploaded);
            $newPaths     = $uploadResult['paths'] ?? [];
            $uploadErrors = $uploadResult['errors'] ?? [];

            foreach ($uploadErrors as $msg) {
                $this->addFlash('warning', $msg);
            }

            // Si user a tenté un upload mais 0 image acceptée => on bloque l'update
            if ($attemptedUpload && count($newPaths) === 0) {
                $this->addFlash('danger', "Aucune image n'a été acceptée (format/poids invalide).");
                return $this->redirectToRoute('admin_catalog_products_edit', ['id' => $id]);
            }

            $manualText = (string) $request->request->get('images', '');
            $manual = array_values(array_filter(array_map('trim', preg_split('/\R/', $manualText) ?: [])));

            $attributes = $this->buildAttributes(
                $request->request->all('attribute_keys'),
                $request->request->all('attribute_values')
            );

            $finalImages = array_values(array_unique(array_merge($existing, $newPaths, $manual)));

            $update = [
                'sku'         => $sku,
                'label'       => $label,
                'slug'        => $this->slugify($label),
                'categoryId'  => $categoryId,
                'unit'        => $unit !== '' ? $unit : null,
                'price_ht'    => $priceHt,
                'price_ttc'   => $priceTtc,
                'description' => $request->request->get('description') ?: null,
                'attributes'  => $attributes,
                'images'      => $finalImages,
                'updatedAt'   => new \MongoDB\BSON\UTCDateTime(),
            ];

            $this->products->update($id, $update);

            if ($uploadErrors) {
                $this->addFlash('warning', 'Produit mis à jour, mais certaines images ont été refusées.');
            } else {
                $this->addFlash('success', 'Produit mis à jour.');
            }

            return $this->redirectToRoute('admin_catalog_products_edit', ['id' => $id]);
        }

        return $this->render('admin/catalog/products/edit.html.twig', [
            'product'      => $product,
            'categories'   => $categories,
            'attributes'   => $product['attributes'] ?? [],
            'description'  => $product['description'] ?? '',
            'images_text'  => implode("\n", (array)($product['images'] ?? [])),
            'price_ht'     => (float)($product['price_ht'] ?? 0),
            'price_ttc'    => (float)($product['price_ttc'] ?? 0),
        ]);
    }

    #[AdminRoute('/{id}/delete', name: 'delete')]
    public function delete(string $id, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete_product_' . $id, (string) $request->request->get('_token'))) {
            $this->products->delete($id);
            $this->addFlash('success', 'Produit supprimé.');
        }

        return $this->redirectToRoute('admin_catalog_products_index');
    }

    private function buildAttributes(array $keys, array $vals): array
    {
        $attributes = [];
        $count = min(max(count($keys), count($vals)), 30); // max 30

        for ($i = 0; $i < $count; $i++) {
            $k = trim((string)($keys[$i] ?? ''));
            $v = trim((string)($vals[$i] ?? ''));

            if ($k === '' || $v === '') continue;

            if (mb_strlen($k) > 50)  $k = mb_substr($k, 0, 50);
            if (mb_strlen($v) > 200) $v = mb_substr($v, 0, 200);

            // interdit clés Mongo “dangereuses”
            if (str_contains($k, '$') || str_contains($k, '.')) continue;

            $attributes[$k] = $v;
        }

        return $attributes;
    }

    private function slugify(string $s): string
    {
        $s = mb_strtolower(trim($s));
        $s = preg_replace('~[^\pL\d]+~u', '-', $s);
        $s = trim($s, '-');
        return $s ?: 'produit';
    }
}
