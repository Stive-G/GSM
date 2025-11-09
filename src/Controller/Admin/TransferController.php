<?php
namespace App\Controller\Admin;

use App\Dto\TransferRequest;
use App\Form\TransferType;
use App\Service\TransferService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;        // <-- import Route
use Symfony\Component\Security\Http\Attribute\IsGranted; // (optionnel)

class TransferController extends AbstractController
{
    public function __construct(private readonly TransferService $transferService) {}

    #[Route('/admin/transfer', name: 'admin_transfer', methods: ['GET','POST'])]
    // #[IsGranted('ROLE_MAGASINIER')]  // optionnel : équivaut à denyAccessUnlessGranted
    public function transfer(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MAGASINIER');

        $dto  = new TransferRequest();
        $form = $this->createForm(TransferType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            try {
                if ($form->isValid()) {
                    $this->transferService->transfer($dto);
                    $this->addFlash('success', 'Transfert effectué avec succès.');
                    return $this->redirectToRoute('admin_transfer');
                }
                $this->addFlash('warning', 'Formulaire invalide. Merci de vérifier les champs.');
            } catch (\Throwable $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('admin/transfer.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
