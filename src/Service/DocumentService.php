<?php

namespace App\Service;

use App\Entity\Document;
use App\Repository\DocumentRepository;

class DocumentService
{
    public function __construct(private DocumentRepository $docRepo) {}

    public function prepareDocument(Document $doc): void
    {
        if (!$doc->getNumero() || trim((string) $doc->getNumero()) === '') {
            $doc->setNumero($this->generateNumero($doc));
        }

        // snapshot: pour chaque ligne, copie ProductRef -> champs SQL
        foreach ($doc->getLignes() as $ligne) {
            $ligne->hydrateFromProductRef();
        }
    }

    private function generateNumero(Document $doc): string
    {
        $prefix = $doc->getType() === Document::TYPE_VENTE ? 'VEN' : 'DEV';
        $day = new \DateTimeImmutable();
        $date = $day->format('Ymd');

        $next = $this->docRepo->countByTypeAndDay($doc->getType(), $day) + 1;
        return sprintf('%s-%s-%04d', $prefix, $date, $next);
    }

    public function processDocument(Document $doc): void {}
    public function reprocessDocument(Document $doc): void {}
}
