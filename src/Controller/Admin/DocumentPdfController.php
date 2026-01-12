<?php

namespace App\Controller\Admin;

use App\Repository\DocumentRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DocumentPdfController extends AbstractController
{
    #[Route('/admin/document/{id}/pdf', name: 'admin_document_pdf', methods: ['GET'])]
    public function pdf(int $id, DocumentRepository $repo): Response
    {
        $doc = $repo->find($id);
        if (!$doc) {
            throw $this->createNotFoundException('Document introuvable');
        }

        // Logo en base64 (évite les problèmes de chemins)
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/assets/img/gsm-logo.png';
        $logoDataUri = '';
        if (is_file($logoPath)) {
            $logoDataUri = 'data:image/png;base64,' . base64_encode((string) file_get_contents($logoPath));
        }

        $html = $this->renderView('pdf/document.html.twig', [
            'doc' => $doc,
            'logoDataUri' => $logoDataUri,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Pagination
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $canvas->page_text(520, 820, "Page {PAGE_NUM} / {PAGE_COUNT}", $font, 9, [0.45, 0.45, 0.45]);

        $numero = $doc->getNumero() ?: (string) $doc->getId();
        $filename = sprintf('%s-%s.pdf', strtolower($doc->getType()), $numero);

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]
        );
    }
}
