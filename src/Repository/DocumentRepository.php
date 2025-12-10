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
     * Retourne les documents d’un type donné
     * Exemple : "devis", "bon_livraison", "facture", "vente", etc.
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.type = :t')
            ->setParameter('t', $type)
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
