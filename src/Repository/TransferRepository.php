<?php
namespace App\Repository;

use App\Entity\Transfer;
use App\Entity\Article;
use App\Entity\Magasin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TransferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transfer::class);
    }

    /** Derniers transferts */
    public function findRecent(int $limit = 20): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    /** Recherche simple par article/magasin/période */
    public function search(?Article $article, ?Magasin $source, ?Magasin $destination, ?\DateTimeImmutable $from, ?\DateTimeImmutable $to): array
    {
        $qb = $this->createQueryBuilder('t');

        if ($article) $qb->andWhere('t.article = :a')->setParameter('a', $article);
        if ($source) $qb->andWhere('t.source = :s')->setParameter('s', $source);
        if ($destination) $qb->andWhere('t.destination = :d')->setParameter('d', $destination);
        if ($from) $qb->andWhere('t.createdAt >= :from')->setParameter('from', $from);
        if ($to) $qb->andWhere('t.createdAt < :to')->setParameter('to', $to);

        return $qb->orderBy('t.createdAt', 'DESC')->getQuery()->getResult();
    }
}
