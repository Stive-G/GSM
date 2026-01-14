<?php

namespace App\Tests\Controller;

use App\Tests\Utils\TestUserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

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

        // login success => redirect
        $this->assertTrue($client->getResponse()->isRedirection());
        $client->followRedirect();

        $client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Dashboard', $client->getResponse()->getContent());
    }

    public function testUserCannotAccessDashboard(): void
    {
        $client    = static::createClient();
        $container = static::getContainer();

        $router = $container->get('router');
        \assert($router instanceof RouterInterface);

        $em     = $container->get('doctrine')->getManager();
        $hasher = $container->get('security.password_hasher');

        TestUserFactory::createUser($em, $hasher);

        // Login user normal (ROLE_USER)
        $crawler = $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Se connecter')->form([
            'email'    => 'user@test.com',
            'password' => 'user123',
        ]);

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirection());
        $client->followRedirect();

        // /admin requires ROLE_VENDEUR; connected but forbidden => AccessDeniedHandler redirects to admin_forbidden
        $client->request('GET', '/admin');

        $this->assertResponseRedirects($router->generate('admin_forbidden'), 302);
    }

    public function testInvalidPasswordRedirectsBackToLogin(): void
    {
        $client    = static::createClient();
        $container = static::getContainer();

        $router = $container->get('router');
        \assert($router instanceof RouterInterface);

        $em     = $container->get('doctrine')->getManager();
        $hasher = $container->get('security.password_hasher');

        TestUserFactory::createUser($em, $hasher);

        $crawler = $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Se connecter')->form([
            'email'    => 'user@test.com',
            'password' => 'mauvais_mot_de_passe',
        ]);

        $client->submit($form);

        // redirect back (login failure)
        $this->assertTrue($client->getResponse()->isRedirection());

        $client->followRedirect();
        $this->assertResponseIsSuccessful();

        // On vérifie qu'on est bien retombé sur la page login (via route générée)
        // (assertResponseRedirects se fait AVANT followRedirect; ici on check le contenu)
        $this->assertStringContainsString('<form method="post"', $client->getResponse()->getContent());
    }

    public function testLogoutClearsSession(): void
    {
        $client    = static::createClient();
        $container = static::getContainer();

        $router = $container->get('router');
        \assert($router instanceof RouterInterface);

        $em     = $container->get('doctrine')->getManager();
        $hasher = $container->get('security.password_hasher');

        TestUserFactory::createAdmin($em, $hasher);

        // Login admin
        $crawler = $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Se connecter')->form([
            'email'    => 'admin@test.com',
            'password' => 'admin123',
        ]);

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirection());
        $client->followRedirect();

        // Vérif : accès admin OK
        $client->request('GET', '/admin');
        $this->assertResponseIsSuccessful();

        // Logout
        $client->request('GET', '/logout');
        $this->assertTrue($client->getResponse()->isRedirection());

        // Après logout, /admin doit rediriger vers login (non connecté => app_login)
        $client->request('GET', '/admin');
        $this->assertResponseRedirects($router->generate('app_login'), 302);
    }
}
