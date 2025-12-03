<?php

namespace App\Controller\Admin\Catalog;

use App\Dto\Catalog\CategoryDto;
use App\Form\Catalog\CategoryType;
use App\Repository\Catalog\CatalogCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[Route('/admin/catalog/categories', name: 'admin_catalog_categories_')]
class CategoryAdminController extends AbstractController
{
    public function __construct(private readonly CatalogCategoryRepository $categoryRepository)
    {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        $categories = $this->categoryRepository->findAll();

        return $this->render('admin/catalog/categories/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request): Response
    {
        $category = new CategoryDto();
        $category->slug = '';
        $form = $this->createForm(CategoryType::class, $category, [
            'category_choices' => $this->categoryRepository->findAll(),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$category->slug) {
                $category->slug = (new AsciiSlugger())->slug($category->name)->lower()->toString();
            }
            $this->categoryRepository->create($category);
            $this->addFlash('success', 'Catégorie créée dans MongoDB');

            return $this->redirectToRoute('admin_catalog_categories_index');
        }

        return $this->render('admin/catalog/categories/form.html.twig', [
            'form' => $form,
            'title' => 'Nouvelle catégorie',
        ]);
    }

    #[Route('/{id}', name: 'edit')]
    public function edit(string $id, Request $request): Response
    {
        $category = $this->categoryRepository->find($id);
        if (!$category) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(CategoryType::class, $category, [
            'category_choices' => array_filter($this->categoryRepository->findAll(), fn (CategoryDto $c) => $c->id !== $id),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$category->slug) {
                $category->slug = (new AsciiSlugger())->slug($category->name)->lower()->toString();
            }
            $this->categoryRepository->update($category);
            $this->addFlash('success', 'Catégorie mise à jour');

            return $this->redirectToRoute('admin_catalog_categories_index');
        }

        return $this->render('admin/catalog/categories/form.html.twig', [
            'form' => $form,
            'title' => 'Éditer la catégorie',
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(string $id, Request $request): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete_category_' . $id, $request->request->get('_token'))) {
            $this->categoryRepository->delete($id);
            $this->addFlash('success', 'Catégorie supprimée');
        }

        return $this->redirectToRoute('admin_catalog_categories_index');
    }
}
