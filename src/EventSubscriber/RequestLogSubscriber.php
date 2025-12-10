<?php

namespace App\EventSubscriber;

use App\Service\LogService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RequestLogSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LogService $logs,
        private readonly TokenStorageInterface $tokens
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onResponse',
        ];
    }

    public function onResponse(ResponseEvent $event): void
    {
        // pour éviter de logger les sous-requêtes (fragments, ESI, etc.)
        if (method_exists($event, 'isMainRequest') && !$event->isMainRequest()) {
            return;
        }
        if (method_exists($event, 'isMasterRequest') && !$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        $route = (string) ($request->attributes->get('_route') ?? '');
        if ($route === '') {
            return;
        }

        $path = $request->getPathInfo() ?? '';
        if (
            str_starts_with($route, '_profiler') ||
            str_starts_with($route, '_wdt') ||
            str_starts_with($path, '/_profiler') ||
            str_starts_with($path, '/_wdt')
        ) {
            return;
        }

        $method = $request->getMethod();

        $user = $this->tokens->getToken()?->getUser();
        if (!\is_object($user)) {
            $user = null;
        }

        try {
            $this->logs->logRequest($route, $method, $user);
        } catch (\Throwable $e) {
            // on ne casse jamais la réponse à cause des logs
        }
    }
}
