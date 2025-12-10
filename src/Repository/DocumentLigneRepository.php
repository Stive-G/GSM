<?php

namespace App\Repository;

use App\Entity\DocumentLigne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DocumentLigne>
 */
class DocumentLigneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentLigne::class);
    }

    /**
     * Calcul du total HT d’un document (quantité × prix unitaire HT)
     */
    public function sumTotalByDocument(int $documentId): float
    {
        $result = $this->createQueryBuilder('l')
            ->select('SUM(l.quantity * l.unitPriceHt) AS total')
            ->andWhere('l.document = :id')
            ->setParameter('id', $documentId)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) $result;
    }
}
