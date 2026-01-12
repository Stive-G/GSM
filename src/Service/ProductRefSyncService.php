<?php

namespace App\Service;

use App\Entity\ProductRef;
use App\Repository\ProductRefRepository;
use Doctrine\ORM\EntityManagerInterface;
use MongoDB\Client as MongoClient;

class ProductRefSyncService
{
    public function __construct(
        private MongoClient $mongo,
        private EntityManagerInterface $em,
        private ProductRefRepository $repo,
        private string $mongoDbName,     // param
        private string $mongoCollection  // param
    ) {}

    public function sync(): int
    {
        $collection = $this->mongo->selectDatabase($this->mongoDbName)
            ->selectCollection($this->mongoCollection);

        $cursor = $collection->find([], [
            'projection' => [
                '_id' => 1,
                'label' => 1,
                'unit' => 1,
                'price_ht' => 1,
            ],
        ]);

        $count = 0;
        $now = new \DateTimeImmutable();

        foreach ($cursor as $p) {
            $mongoId = (string) $p->_id;

            $label = (string) ($p->label ?? '');
            $unit  = (string) ($p->unit ?? '');
            $price = $p->price_ht ?? null;

            if ($label === '' || $unit === '' || $price === null) {
                continue;
            }

            $ref = $this->repo->findOneBy(['mongoId' => $mongoId]) ?? new ProductRef();

            $ref->setMongoId($mongoId)
                ->setLabel($label)
                ->setUnit($unit)
                ->setPriceHt(number_format((float) $price, 2, '.', ''))
                ->setSyncedAt($now);

            $this->em->persist($ref);
            $count++;
        }

        $this->em->flush();
        return $count;
    }
}
