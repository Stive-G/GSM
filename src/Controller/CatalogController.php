<?php

namespace App\Controller;

use App\Repository\Catalog\CatalogCategoryRepository;
use App\Repository\Catalog\CatalogProductRepository;
use App\Repository\Catalog\CatalogStockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CatalogController extends AbstractController
{
    public function __construct(
        private readonly CatalogCategoryRepository $categoryRepository,
        private readonly CatalogProductRepository $productRepository,
        private readonly CatalogStockRepository $stockRepository,
    ) {
    }

    #[Route('/catalogue', name: 'catalog_index')]
    public function index(): Response
    {
        $categories = $this->categoryRepository->findTree();

        return $this->render('catalog/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/catalogue/categorie/{slug}', name: 'catalog_category')]
    public function category(string $slug, Request $request): Response
    {
        $category = $this->categoryRepository->findBySlug($slug);
        if (!$category) {
            throw $this->createNotFoundException();
        }

        $filters = [
            'categoryId' => $category['_id'],
            'brand' => $request->query->get('brand'),
            'priceMin' => $request->query->get('priceMin'),
            'priceMax' => $request->query->get('priceMax'),
            'attributes' => $request->query->all('attr'),
            'sort' => $request->query->get('sort'),
            'text' => $request->query->get('q'),
        ];

        $products = $this->productRepository->search($filters, ['limit' => 100]);

        return $this->render('catalog/category.html.twig', [
            'category' => $category,
            'products' => $products,
            'filters' => $filters,
        ]);
    }

    #[Route('/catalogue/produit/{slug}', name: 'catalog_product')]
    public function product(string $slug): Response
    {
        $product = $this->productRepository->findBySlug($slug);
        if (!$product) {
            throw $this->createNotFoundException();
        }

        $stocks = $this->stockRepository->findByProduct($product['_id']);

        return $this->render('catalog/product.html.twig', [
            'product' => $product,
            'stocks' => $stocks,
        ]);
    }
}
