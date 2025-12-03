<?php

namespace App\Controller\Admin\Catalog;

use App\Dto\Catalog\ProductDto;
use App\Dto\Catalog\StockDto;
use App\Form\Catalog\ProductType;
use App\Form\Catalog\StockType;
use App\Repository\Catalog\CatalogCategoryRepository;
use App\Repository\Catalog\CatalogMagasinRepository;
use App\Repository\Catalog\CatalogProductRepository;
use App\Repository\Catalog\CatalogStockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[Route('/admin/catalog/products', name: 'admin_catalog_products_')]
class ProductAdminController extends AbstractController
{
    public function __construct(
        private readonly CatalogProductRepository $productRepository,
        private readonly CatalogCategoryRepository $categoryRepository,
        private readonly CatalogStockRepository $stockRepository,
        private readonly CatalogMagasinRepository $magasinRepository,
    ) {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        $products = $this->productRepository->findAll();

        return $this->render('admin/catalog/products/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request): Response
    {
        $product = new ProductDto();
        $product->variants = [];

        $form = $this->createForm(ProductType::class, $product, [
            'category_choices' => $this->categoryRepository->findAll(),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$product->slug) {
                $product->slug = (new AsciiSlugger())->slug($product->name)->lower()->toString();
            }
            $this->productRepository->create($product);
            $this->addFlash('success', 'Produit créé');

            return $this->redirectToRoute('admin_catalog_products_index');
        }

        return $this->render('admin/catalog/products/form.html.twig', [
            'form' => $form,
            'title' => 'Nouveau produit',
        ]);
    }

    #[Route('/{id}', name: 'edit')]
    public function edit(string $id, Request $request): Response
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ProductType::class, $product, [
            'category_choices' => $this->categoryRepository->findAll(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$product->slug) {
                $product->slug = (new AsciiSlugger())->slug($product->name)->lower()->toString();
            }
            $this->productRepository->update($product);
            $this->addFlash('success', 'Produit mis à jour');

            return $this->redirectToRoute('admin_catalog_products_index');
        }

        return $this->render('admin/catalog/products/form.html.twig', [
            'form' => $form,
            'title' => 'Éditer le produit',
            'product' => $product,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(string $id, Request $request): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete_product_' . $id, $request->request->get('_token'))) {
            $this->productRepository->delete($id);
            $this->addFlash('success', 'Produit supprimé');
        }

        return $this->redirectToRoute('admin_catalog_products_index');
    }

    #[Route('/{id}/stocks', name: 'stocks')]
    public function manageStock(string $id, Request $request): Response
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException();
        }

        $stockDto = new StockDto(productId: $product->id);
        $form = $this->createForm(StockType::class, $stockDto, [
            'variant_choices' => $product->variants,
            'magasin_choices' => $this->magasinRepository->findAll(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->stockRepository->upsert($stockDto);
            $this->addFlash('success', 'Stock mis à jour');

            return $this->redirectToRoute('admin_catalog_products_stocks', ['id' => $id]);
        }

        $stocks = $this->stockRepository->findByProduct($id);

        return $this->render('admin/catalog/products/stocks.html.twig', [
            'product' => $product,
            'form' => $form,
            'stocks' => $stocks,
        ]);
    }
}
