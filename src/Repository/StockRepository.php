<?php

namespace App\Repository;

use App\Entity\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    /**
     * Retourne les lignes de stock dont la quantité est < seuil.
     *
     * @param float $threshold Seuil d’alerte (ex: 5)
     * @return Stock[]
     */
    public function findLowStock(float $threshold = 5): array
    {
        // NOTE: quantity est un DECIMAL en DB—Doctrine le mappe souvent en string,
        // mais la comparaison SQL s’effectue correctement côté SGBD.
        return $this->createQueryBuilder('s')
            ->andWhere('s.quantity < :t')
            ->setParameter('t', $threshold) // on laisse le SGBD comparer numériquement
            ->orderBy('s.quantity', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les lignes sous le seuil.
     */
    public function countLowStock(float $threshold = 5): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.quantity < :t')
            ->setParameter('t', $threshold)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
