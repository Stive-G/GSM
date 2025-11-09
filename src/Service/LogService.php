<?php
namespace App\Service;

use MongoDB\Client;
use MongoDB\Collection;
use Symfony\Component\Security\Core\User\UserInterface;

class LogService
{
    private Collection $col;

    public function __construct(Client $client, string $dbName)
    {
        $this->col = $client->selectCollection($dbName, 'actions'); // collection "actions"
        // Index utiles
        $this->col->createIndex(['ts' => -1]);
        $this->col->createIndex(['user.id' => 1]);
        $this->col->createIndex(['route' => 1]);
        $this->col->createIndex(['type' => 1]); // 'request' | 'entity'
    }

    public function logRequest(array $data): void
    {
        $data['type'] = 'request';
        $data['ts'] = new \MongoDB\BSON\UTCDateTime((int)(microtime(true) * 1000));
        $this->col->insertOne($data);
    }

    public function logEntity(string $event, object $entity, array $changes, ?UserInterface $user = null): void
    {
        /** @var object $user */
        $doc = [
            'type'    => 'entity',
            'event'   => $event, // created|updated|deleted
            'entity'  => get_class($entity),
            'id'      => method_exists($entity, 'getId') ? $entity->getId() : null,
            'changes' => $changes,
            'user'    => $user ? [
                'id'    => method_exists($user, 'getId') ? $user->getId() : null,
                'email' => method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : null,
                'roles' => method_exists($user, 'getRoles') ? $user->getRoles() : [],
            ] : null,
            'ts' => new \MongoDB\BSON\UTCDateTime((int)(microtime(true) * 1000)),
        ];
        $this->col->insertOne($doc);
    }

    public function find(array $filter = [], array $options = []): array
    {
        $cursor = $this->col->find($filter, $options);
        return iterator_to_array($cursor);
    }

    public function count(array $filter = []): int
    {
        return $this->col->countDocuments($filter);
    }
}
