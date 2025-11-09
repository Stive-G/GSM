<?php

namespace App\Service;

use App\Dto\TransferRequest;
use App\Entity\MouvementStock;
use App\Entity\Stock;
use Doctrine\ORM\EntityManagerInterface;

class TransferService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly StockService $stockService
    ) {}

    /**
     * Réalise un transfert atomique (transaction) :
     *  - OUT du magasin source
     *  - IN  du magasin destination
     * @throws \RuntimeException si invalidation (magasin identiques, stock insuffisant, etc.)
     */
    public function transfer(TransferRequest $req): void
    {
        if ($req->source?->getId() === $req->destination?->getId()) {
            throw new \RuntimeException("Le magasin source et le magasin destination doivent être différents.");
        }
        if (($req->quantity ?? 0) <= 0) {
            throw new \RuntimeException("La quantité doit être strictement positive.");
        }

        $stockRepo = $this->em->getRepository(\App\Entity\Stock::class);
        $sourceStock = $stockRepo->findOneBy([
            'article' => $req->article,
            'conditionnement' => $req->conditionnement,
            'magasin' => $req->source,
        ]);
        $available = (float)($sourceStock?->getQuantity() ?? '0');
        if ($available < (float)$req->quantity) {
            throw new \RuntimeException(sprintf(
                "Stock insuffisant dans le magasin source (disponible: %s, demandé: %s).",
                $available,
                $req->quantity
            ));
        }

        $this->em->beginTransaction();
        try {
            // OUT source
            $out = (new \App\Entity\MouvementStock())
                ->setArticle($req->article)
                ->setConditionnement($req->conditionnement)
                ->setMagasin($req->source)
                ->setType(\App\Entity\MouvementStock::TYPE_OUT)
                ->setQuantity((string)$req->quantity)
                ->setComment($req->comment ? ('Transfert OUT — ' . $req->comment) : 'Transfert OUT');

            $this->stockService->applyMovement($out);
            $this->em->persist($out);

            // IN destination
            $in = (new \App\Entity\MouvementStock())
                ->setArticle($req->article)
                ->setConditionnement($req->conditionnement)
                ->setMagasin($req->destination)
                ->setType(\App\Entity\MouvementStock::TYPE_IN)
                ->setQuantity((string)$req->quantity)
                ->setComment($req->comment ? ('Transfert IN — ' . $req->comment) : 'Transfert IN');

            $this->stockService->applyMovement($in);
            $this->em->persist($in);

            // TRANSFER log
            $transfer = (new \App\Entity\Transfer())
                ->setArticle($req->article)
                ->setConditionnement($req->conditionnement)
                ->setSource($req->source)
                ->setDestination($req->destination)
                ->setQuantity((string)$req->quantity)
                ->setComment($req->comment)
                ->setOutMovement($out)
                ->setInMovement($in);

            $this->em->persist($transfer);

            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }
}
