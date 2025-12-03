<?php

namespace App\Controller\Admin\Catalog;

use App\Dto\Catalog\MagasinDto;
use App\Form\Catalog\MagasinType;
use App\Repository\Catalog\CatalogMagasinRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/catalog/magasins', name: 'admin_catalog_magasins_')]
class MagasinAdminController extends AbstractController
{
    public function __construct(private readonly CatalogMagasinRepository $magasinRepository)
    {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/catalog/magasins/index.html.twig', [
            'magasins' => $this->magasinRepository->findAll(),
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request): Response
    {
        $magasin = new MagasinDto();
        $form = $this->createForm(MagasinType::class, $magasin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->magasinRepository->create($magasin);
            $this->addFlash('success', 'Magasin créé');

            return $this->redirectToRoute('admin_catalog_magasins_index');
        }

        return $this->render('admin/catalog/magasins/form.html.twig', [
            'form' => $form,
            'title' => 'Nouveau magasin',
        ]);
    }

    #[Route('/{id}', name: 'edit')]
    public function edit(string $id, Request $request): Response
    {
        $magasin = $this->magasinRepository->find($id);
        if (!$magasin) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(MagasinType::class, $magasin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->magasinRepository->update($magasin);
            $this->addFlash('success', 'Magasin mis à jour');

            return $this->redirectToRoute('admin_catalog_magasins_index');
        }

        return $this->render('admin/catalog/magasins/form.html.twig', [
            'form' => $form,
            'title' => 'Éditer le magasin',
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(string $id, Request $request): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete_magasin_' . $id, $request->request->get('_token'))) {
            $this->magasinRepository->delete($id);
            $this->addFlash('success', 'Magasin supprimé');
        }

        return $this->redirectToRoute('admin_catalog_magasins_index');
    }
}
