<?php
namespace App\Repository;

use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Document>
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    /**
     * Retourne les ventes (type = VENTE)
     */
    public function findVentes(): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.type = :t')
            ->setParameter('t', Document::TYPE_VENTE)
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les devis
     */
    public function findDevis(): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.type = :t')
            ->setParameter('t', Document::TYPE_DEVIS)
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
