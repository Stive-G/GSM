<?php

namespace App\EventSubscriber;

use App\Service\LogService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EntityLogSubscriber implements EventSubscriber
{
    public function __construct(
        private readonly LogService $logs,
        private readonly TokenStorageInterface $tokens
    ) {}

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $user   = $this->tokens->getToken()?->getUser();

        // création : pas besoin de champs modifiés
        $this->logs->logEntity(
            'created',
            $entity,
            [],
            \is_object($user) ? $user : null
        );
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity        = $args->getObject();
        $objectManager = $args->getObjectManager();

        $fieldsChanged = [];
        if ($objectManager instanceof EntityManagerInterface) {
            $changeset = $objectManager->getUnitOfWork()->getEntityChangeSet($entity);

            // On garde uniquement la liste des noms de champs modifiés
            $fieldsChanged = array_keys($changeset ?? []);
        }

        $user = $this->tokens->getToken()?->getUser();

        $this->logs->logEntity(
            'updated',
            $entity,
            $fieldsChanged,
            \is_object($user) ? $user : null
        );
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        $user   = $this->tokens->getToken()?->getUser();

        $this->logs->logEntity(
            'deleted',
            $entity,
            [],
            \is_object($user) ? $user : null
        );
    }
}
