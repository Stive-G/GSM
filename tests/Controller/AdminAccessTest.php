<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminAccessTest extends WebTestCase
{
    public function testAnonymousUserIsRedirectedFromAdmin(): void
    {
        $client = static::createClient();

        // On tente d'accéder à /admin sans être connecté
        $client->request('GET', '/admin');

        // On vérifie qu'on a bien une redirection (vers la page de login par exemple)
        $this->assertTrue(
            $client->getResponse()->isRedirection(),
            'Un utilisateur anonyme devrait être redirigé depuis /admin.'
        );

        // Optionnel : vérifier l’URL de redirection si tu veux
        // $this->assertStringContainsString('/login', $client->getResponse()->headers->get('Location'));
    }
}
