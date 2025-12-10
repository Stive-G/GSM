<?php

namespace App\Service;

use App\Entity\ActionLog;
use Doctrine\ORM\EntityManagerInterface;

class LogService
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {}

    /**
     * On garde juste un petit snapshot de l'utilisateur :
     *  - id
     *  - email
     *  - roles
     */
    private function normalizeUser(?object $user): ?array
    {
        if (!$user) {
            return null;
        }

        return [
            'id'    => method_exists($user, 'getId') ? $user->getId() : null,
            'email' => method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : null,
            'roles' => method_exists($user, 'getRoles') ? $user->getRoles() : [],
        ];
    }

    /**
     * Log d’une action “route” :
     *  - route Symfony
     *  - méthode HTTP
     *  - user
     *  → PAS d’IP, PAS de body, PAS de query string.
     */
    public function logRequest(string $route, string $method, ?object $user): void
    {
        $log = new ActionLog();
        $log->setType('route');
        $log->setRoute($route);
        $log->setUser($this->normalizeUser($user));

        $payload = [
            'method' => $method,
            // tu peux rajouter un mini contexte si tu veux :
            // 'scope'  => 'backoffice', etc.
        ];
        $log->setPayload($payload);

        $this->em->persist($log);
        $this->em->flush();
    }

    /**
     * Log d’un changement Doctrine sur une entité.
     *
     * $eventType: created / updated / deleted
     * $fieldsChanged: liste des noms de champs (pas les valeurs).
     */
    public function logEntity(string $eventType, object $entity, array $fieldsChanged, ?object $user): void
    {
        $log = new ActionLog();
        $log->setType('entity_' . $eventType);
        // on met le FQCN dans route pour avoir le contexte
        $log->setRoute($entity::class);
        $log->setUser($this->normalizeUser($user));

        $payload = [
            'entityClass'   => $entity::class,
            'entityId'      => method_exists($entity, 'getId') ? $entity->getId() : null,
            'action'        => $eventType,
            'fieldsChanged' => $fieldsChanged, // juste les noms des champs, pas les valeurs
        ];

        $log->setPayload($payload);

        $this->em->persist($log);
        $this->em->flush();
    }
}
