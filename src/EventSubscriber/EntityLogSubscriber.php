<?php
namespace App\EventSubscriber;

use App\Service\LogService;
use Doctrine\Common\EventSubscriber;
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
        return [ Events::postPersist, Events::postUpdate, Events::postRemove ];
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $user = $this->tokens->getToken()?->getUser();
        $this->logs->logEntity('created', $entity, [], is_object($user) ? $user : null);
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $objectManager = $args->getObjectManager();
        $changeset = [];
        if ($objectManager instanceof \Doctrine\ORM\EntityManagerInterface) {
            $changeset = $objectManager->getUnitOfWork()->getEntityChangeSet($entity);
        }
        // Nettoyage de valeurs potentiellement sensibles
        foreach ($changeset as $field => &$change) {
            if (in_array($field, ['password','motDePasse','token','secret'], true)) {
                $change = ['***', '***'];
            }
        }
        $user = $this->tokens->getToken()?->getUser();
        $this->logs->logEntity('updated', $entity, $changeset, is_object($user) ? $user : null);
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        $user = $this->tokens->getToken()?->getUser();
        $this->logs->logEntity('deleted', $entity, [], is_object($user) ? $user : null);
    }
}
