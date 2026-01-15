<?php

namespace App\Controller\Admin\Catalog;

use App\Service\Catalog\CategoryCatalogService;
use MongoDB\BSON\UTCDateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_DIRECTION')]
#[AdminRoute('/catalog/categories', name: 'catalog_categories')]
final class CategoryAdminController extends AbstractController
{
    public function __construct(private readonly CategoryCatalogService $categories) {}

    #[AdminRoute('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/catalog/categories/index.html.twig', [
            'categories' => $this->categories->findAll(),
        ]);
    }

    #[AdminRoute('/new', name: 'new')]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $name = trim((string)$request->request->get('name'));

            if (!$this->isCsrfTokenValid('catalog_category_new', (string)$request->request->get('_token'))) {
                throw $this->createAccessDeniedException('CSRF invalide.');
            }

            if (mb_strlen($name) > 120) {
                $this->addFlash('danger', 'Nom trop long (120 max).');
                return $this->render('admin/catalog/categories/new.html.twig');
            }

            if ($name === '') {
                $this->addFlash('danger', 'Le nom est obligatoire.');
            } else {
                $now = new UTCDateTime();
                $this->categories->create([
                    'name' => $name,
                    'slug' => $this->slugify($name),
                    'parentId' => null,
                    'createdAt' => $now,
                    'updatedAt' => $now,
                ]);

                $this->addFlash('success', 'Catégorie créée.');
                return $this->redirectToRoute('admin_catalog_categories_index');
            }
        }

        return $this->render('admin/catalog/categories/new.html.twig');
    }

    private function slugify(string $s): string
    {
        $s = mb_strtolower(trim($s));
        $s = preg_replace('~[^\pL\d]+~u', '-', $s);
        return trim($s, '-') ?: 'categorie';
    }
}
