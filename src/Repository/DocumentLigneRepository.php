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
     * Calcul du total TTC d’un document (simple addition quantité × prix)
     */
    public function sumTotalByDocument(int $documentId): float
    {
        $result = $this->createQueryBuilder('l')
            ->select('SUM(l.quantity * l.unitPrice) as total')
            ->andWhere('l.document = :id')
            ->setParameter('id', $documentId)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) $result;
    }
}
