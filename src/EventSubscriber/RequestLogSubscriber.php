<?php

namespace App\EventSubscriber;

use App\Service\LogService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RequestLogSubscriber implements EventSubscriberInterface
{
    private ?array $userInfo = null; // ✅ sauvegarde du user avant terminate

    public function __construct(
        private readonly LogService $logs,
        private readonly TokenStorageInterface $tokens
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST   => 'onRequest',   // 🔹 avant le début
            KernelEvents::TERMINATE => 'onTerminate', // 🔹 après la réponse
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        $token = $this->tokens->getToken();
        /** @var object|null $user */
        $user  = $token?->getUser();

        if (is_object($user)) {
            $this->userInfo = [
                'id'    => method_exists($user, 'getId') ? $user->getId() : null,
                'email' => method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : null,
                'roles' => method_exists($user, 'getRoles') ? $user->getRoles() : [],
            ];
        }
    }

    public function onTerminate(TerminateEvent $event): void
    {
        $req = $event->getRequest();
        $res = $event->getResponse();

        // 🔹 Filtres (tu gardes les tiens)
        $route = (string) ($req->attributes->get('_route') ?? '');
        $path  = $req->getPathInfo() ?? '';
        if (
            $route === '' ||
            str_starts_with($route, '_profiler') ||
            str_starts_with($route, '_wdt') ||
            str_starts_with($path, '/_wdt') ||
            str_starts_with($path, '/_profiler') ||
            str_starts_with($path, '/build/') ||
            str_starts_with($path, '/assets/') ||
            $path === '/health' || $path === '/healthz'
        ) {
            return;
        }

        // 🔹 plus de session ici
        $payload = null;
        if (in_array($req->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            if (0 === strpos((string)$req->headers->get('Content-Type'), 'application/json')) {
                $raw = (string) $req->getContent(false);
                $decoded = json_decode($raw, true);
                $payload = is_array($decoded) ? $decoded : ['_raw' => substr($raw, 0, 4096)];
            } else {
                $payload = $req->request->all();
                if ($req->files->count() > 0) {
                    $payload['_files'] = array_map(
                        fn($f) => is_array($f) ? '[array files]' : ($f?->getClientOriginalName() ?? '[uploaded file]'),
                        $req->files->all()
                    );
                }
            }

            $sensitive = ['_csrf_token', 'password', 'plainPassword', 'currentPassword', 'token', 'secret', 'apiKey', 'apikey'];
            foreach ($sensitive as $k) {
                if (isset($payload[$k])) $payload[$k] = '***';
            }
            $json = json_encode($payload);
            if (is_string($json) && strlen($json) > 8192) {
                $payload = ['_truncated' => true];
            }
        }

        $doc = [
            'request_id' => bin2hex(random_bytes(8)), // ✅ OK PHP natif
            'user'   => $this->userInfo,
            'route'  => $route,
            'path'   => $path,
            'method' => $req->getMethod(),
            'status' => $res->getStatusCode(),
            'ip'     => $req->getClientIp(),
            'agent'  => $req->headers->get('User-Agent'),
            'query'  => $req->query->all(),
            'body'   => $payload,
            'createdAt' => new \MongoDB\BSON\UTCDateTime()
        ];

        try {
            $this->logs->logRequest($doc);
        } catch (\Throwable $e) {
            // on ignore pour ne pas casser la réponse
            // error_log('[logs] '.$e->getMessage());
        }
    }
}
