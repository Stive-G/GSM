<?php

namespace App\Tests\Controller;

use App\Tests\Utils\TestUserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminLoginTest extends WebTestCase
{
    public function testAdminCanAccessDashboard(): void
    {
        $client    = static::createClient();
        $container = static::getContainer();

        $em     = $container->get('doctrine')->getManager();
        $hasher = $container->get('security.password_hasher');

        TestUserFactory::createAdmin($em, $hasher);

        $crawler = $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Se connecter')->form([
            'email'    => 'admin@test.com',
            'password' => 'admin123',
        ]);

        $client->submit($form);
        $client->followRedirect();

        $client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Dashboard', $client->getResponse()->getContent());
    }

    public function testUserCannotAccessDashboard(): void
    {
        $client    = static::createClient();
        $container = static::getContainer();

        $em     = $container->get('doctrine')->getManager();
        $hasher = $container->get('security.password_hasher');

        TestUserFactory::createUser($em, $hasher);

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            'email'    => 'user@test.com',
            'password' => 'user123',
        ]);

        $client->submit($form);
        $client->followRedirect();

        $client->request('GET', '/admin');

        $this->assertSame(403, $client->getResponse()->getStatusCode());
    }

    public function testInvalidPasswordRedirectsBackToLogin(): void
    {
        $client    = static::createClient();
        $container = static::getContainer();

        $em     = $container->get('doctrine')->getManager();
        $hasher = $container->get('security.password_hasher');

        TestUserFactory::createUser($em, $hasher);

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            'email'    => 'user@test.com',
            'password' => 'mauvais_mot_de_passe',
        ]);

        $client->submit($form);

        // 1) il doit y avoir une redirection (vers la page de login)
        $this->assertTrue($client->getResponse()->isRedirection());

        // 2) après redirection, on est bien de nouveau sur /login
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            '<form method="post"',
            $client->getResponse()->getContent(),
        );
        // On ne teste pas le texte "Identifiants invalides" car ton template ne l'affiche pas (pour l’instant).
    }

    public function testLogoutClearsSession(): void
    {
        $client    = static::createClient();
        $container = static::getContainer();

        $em     = $container->get('doctrine')->getManager();
        $hasher = $container->get('security.password_hasher');

        TestUserFactory::createAdmin($em, $hasher);

        // Login admin
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            'email'    => 'admin@test.com',
            'password' => 'admin123',
        ]);
        $client->submit($form);
        $client->followRedirect();

        // Vérif : accès admin OK
        $client->request('GET', '/admin');
        $this->assertResponseIsSuccessful();

        // Logout (adapter l'URL si besoin)
        $client->request('GET', '/logout');
        $this->assertTrue($client->getResponse()->isRedirection());

        // Ne pas followRedirect ici pour éviter le 404 sur /
        // On vérifie simplement qu'après logout, un accès /admin n'est plus OK
        $client->request('GET', '/admin');
        $this->assertTrue(
            $client->getResponse()->isRedirection() || $client->getResponse()->getStatusCode() === 403
        );
    }
}
