<?php

namespace App\Service;

use App\Entity\Document;
use Doctrine\ORM\EntityManagerInterface;

class DocumentService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    /**
     * Pour l’instant : pas d’impact stock automatique.
     * On laisse la création de mouvements se faire via MouvementStockCrudController.
     */
    public function processDocument(Document $doc): void
    {
        // TODO plus tard : générer des MouvementStock à partir des lignes
        // en utilisant productIdMongo / productLabel / unit / quantity.
    }

    public function reprocessDocument(Document $doc): void
    {
        // Stratégie actuelle : on interdit l’édition d’une vente si tu décides plus tard.
        // Tu peux aussi laisser vide pour ne rien bloquer.
    }
}
