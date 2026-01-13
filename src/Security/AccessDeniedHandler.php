<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

final class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(
        private RouterInterface $router,
        private TokenStorageInterface $tokenStorage,
    ) {}

    public function handle(Request $request, AccessDeniedException $exception): RedirectResponse
    {
        $token = $this->tokenStorage->getToken();
        $user  = $token?->getUser();

        // Non connecté -> login
        if (!$token || !$user || $user === 'anon.') {
            return new RedirectResponse($this->router->generate('app_login'));
        }

        // Connecté mais interdit -> page dédiée
        return new RedirectResponse($this->router->generate('admin_forbidden'));
    }
}
