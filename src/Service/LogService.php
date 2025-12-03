<?php

namespace App\Service;

use App\Entity\ActionLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class LogService
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function logRequest(array $data): void
    {
        $log = new ActionLog();
        $log->setType('request');
        $log->setRoute($data['route'] ?? null);
        $log->setUser($data['user'] ?? null);
        $log->setPayload($data);

        $this->em->persist($log);
        $this->em->flush();
    }

    public function logEntity(string $event, object $entity, array $changes, ?UserInterface $user = null): void
    {
        $payload = [
            'event' => $event,
            'entity' => get_class($entity),
            'id' => method_exists($entity, 'getId') ? $entity->getId() : null,
            'changes' => $changes,
        ];

        $log = new ActionLog();
        $log->setType('entity');
        $log->setPayload($payload);

        if ($user) {
            $log->setUser([
                'id' => method_exists($user, 'getId') ? $user->getId() : null,
                'email' => method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : null,
                'roles' => method_exists($user, 'getRoles') ? $user->getRoles() : [],
            ]);
        }

        $this->em->persist($log);
        $this->em->flush();
    }

    public function find(array $filter = [], array $options = []): array
    {
        $repository = $this->em->getRepository(ActionLog::class);
        $criteria = array_filter([
            'type' => $filter['type'] ?? null,
            'route' => $filter['route'] ?? null,
        ], static fn ($value) => $value !== null && $value !== '');

        $items = $repository->findBy(
            $criteria,
            $options['sort'] ?? ['createdAt' => 'DESC'],
            $options['limit'] ?? null,
            $options['offset'] ?? null
        );

        if (!empty($filter['email'])) {
            $items = array_filter($items, static fn (ActionLog $log) => ($log->getUser()['email'] ?? null) === $filter['email']);
        }

        return $items;
    }

    public function count(array $filter = []): int
    {
        $repository = $this->em->getRepository(ActionLog::class);
        $criteria = array_filter([
            'type' => $filter['type'] ?? null,
            'route' => $filter['route'] ?? null,
        ], static fn ($value) => $value !== null && $value !== '');

        $count = $repository->count($criteria);

        if (!empty($filter['email'])) {
            $count = count(array_filter(
                $repository->findBy($criteria),
                static fn (ActionLog $log) => ($log->getUser()['email'] ?? null) === $filter['email']
            ));
        }

        return $count;
    }
}
