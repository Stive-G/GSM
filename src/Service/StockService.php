<?php

namespace App\Service;

use App\Entity\Stock;
use App\Entity\MouvementStock;
use App\Entity\Article;
use App\Entity\Conditionnement;
use App\Entity\Magasin;
use Doctrine\ORM\EntityManagerInterface;

class StockService
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Retourne un stock existant ou le crée s'il n'existe pas encore.
     */
    public function getOrCreateStock(Article $article, Conditionnement $cond, Magasin $magasin): Stock
    {
        $repo = $this->em->getRepository(Stock::class);

        /** @var Stock|null $stock */
        $stock = $repo->findOneBy([
            'article' => $article,
            'conditionnement' => $cond,
            'magasin' => $magasin,
        ]);

        if (!$stock) {
            $stock = (new Stock())
                ->setArticle($article)
                ->setConditionnement($cond)
                ->setMagasin($magasin)
                ->setQuantity('0.0000');
            $this->em->persist($stock);
        }

        return $stock;
    }

    /**
     * Applique un mouvement de stock (entrée, sortie, perte, ajustement).
     */
    public function applyMovement(MouvementStock $mvt): void
    {
        $stock = $this->getOrCreateStock(
            $mvt->getArticle(),
            $mvt->getConditionnement(),
            $mvt->getMagasin()
        );

        $q = (float)$stock->getQuantity();
        $delta = (float)$mvt->getQuantity();

        switch ($mvt->getType()) {
            case MouvementStock::TYPE_IN:
                $q += $delta;
                break;

            case MouvementStock::TYPE_OUT:
            case MouvementStock::TYPE_LOSS:
                $q -= $delta;
                if ($q < 0) {
                    throw new \RuntimeException('Stock négatif interdit');
                }
                break;

            case MouvementStock::TYPE_ADJUST:
                if ($delta < 0) {
                    throw new \RuntimeException('Ajustement négatif interdit');
                }
                $q = $delta;
                break;

            default:
                throw new \InvalidArgumentException('Type de mouvement invalide');
        }

        $stock->setQuantity(number_format($q, 4, '.', ''));
        $this->checkLowStock($stock); // 🔔 Vérifie le niveau de stock après chaque mouvement
    }

    /**
     * Annule un mouvement de stock (utile en cas d'erreur ou de suppression).
     */
    public function revertMovement(MouvementStock $mvt): void
    {
        $stock = $this->getOrCreateStock(
            $mvt->getArticle(),
            $mvt->getConditionnement(),
            $mvt->getMagasin()
        );

        $q = (float)$stock->getQuantity();
        $delta = (float)$mvt->getQuantity();

        switch ($mvt->getType()) {
            case MouvementStock::TYPE_IN:
                $q -= $delta;
                break;

            case MouvementStock::TYPE_OUT:
            case MouvementStock::TYPE_LOSS:
                $q += $delta;
                break;

            case MouvementStock::TYPE_ADJUST:
                throw new \RuntimeException("Revert d'un ajustement non supporté automatiquement");
        }

        if ($q < 0) {
            throw new \RuntimeException('Stock négatif interdit (revert)');
        }

        $stock->setQuantity(number_format($q, 4, '.', ''));
    }

    /**
     * Vérifie si un stock est en dessous du seuil critique et loggue un avertissement.
     */
    private function checkLowStock(Stock $stock, float $threshold = 5): void
    {
        if ((float)$stock->getQuantity() < $threshold) {
            $msg = sprintf(
                "Stock bas : %s (%s) - Magasin %s : %.2f unités restantes",
                $stock->getArticle()->getLabel(),
                $stock->getConditionnement()->getLabel() ?? 'N/A',
                $stock->getMagasin()->getName() ?? 'N/A',
                $stock->getQuantity()
            );

            // Exemple de log (Monolog / stderr)
            error_log($msg);
        }
    }
}
