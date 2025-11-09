<?php
namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * Exemple : recherche d’un client par nom partiel
     */
    public function searchByName(string $term): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.name LIKE :term')
            ->setParameter('term', "%$term%")
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
