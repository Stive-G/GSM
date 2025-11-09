<?php
namespace App\Service;

use App\Entity\Document;
use App\Entity\MouvementStock;
use Doctrine\ORM\EntityManagerInterface;

class DocumentService
{
    public function __construct(
        private EntityManagerInterface $em,
        private StockService $stockService
    ) {}

    /**
     * Applique l'impact stock pour un document VENTE (création de OUT par ligne)
     */
    public function processDocument(Document $doc): void
    {
        if ($doc->getType() !== Document::TYPE_VENTE) {
            return; // DEVIS n'impacte pas le stock
        }

        foreach ($doc->getLignes() as $ligne) {
            $mvt = (new MouvementStock())
                ->setArticle($ligne->getArticle())
                ->setConditionnement($ligne->getConditionnement())
                ->setMagasin($this->guessMagasinForSale()) // TODO: sélection magasin si multi
                ->setType(MouvementStock::TYPE_OUT)
                ->setQuantity($ligne->getQuantity())
                ->setComment(sprintf('VENTE doc#%d - %s', $doc->getId() ?? 0, $ligne->getDesignation()));

            $this->stockService->applyMovement($mvt);
            $this->em->persist($mvt);
        }

        $this->em->flush();
    }

    /**
     * Simple re-process: (stratégie naïve)
     * - Dans un vrai monde, on comparerait anciennes lignes/nouvelles pour faire revert/apply.
     * - Ici: on ajoute de nouveaux mouvements si besoin (ou on interdit l'édition après vente).
     */
    public function reprocessDocument(Document $doc): void
    {
        // Stratégie simple: on interdit l'édition d'une vente (recommandé)
        if ($doc->getType() === Document::TYPE_VENTE) {
            throw new \RuntimeException("Edition d'une vente non autorisée. Annuler et recréer.");
        }
    }

    private function guessMagasinForSale(): \App\Entity\Magasin
    {
        // TODO: remonter le magasin depuis l'utilisateur, un paramètre, ou un champ "magasin" sur Document.
        // Pour l’instant, on prend le premier magasin existant.
        $mag = $this->em->getRepository(\App\Entity\Magasin::class)->findOneBy([]);
        if (!$mag) throw new \RuntimeException('Aucun magasin défini pour la vente');
        return $mag;
    }
}
