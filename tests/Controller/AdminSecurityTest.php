<?php

namespace App\Tests\Controller;

use App\Tests\Utils\TestUserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminSecurityTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $hasher;

    protected function setUp(): void
    {
        parent::setUp();

        // 1) Créer le client AVANT d'accéder au container
        $this->client = static::createClient();

        // 2) Récupérer les services
        $container    = static::getContainer();
        $this->em     = $container->get('doctrine')->getManager();
        $this->hasher = $container->get('security.password_hasher');
    }

    private function loginAs(string $email, string $password): void
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            'email'    => $email,
            'password' => $password,
        ]);

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirection());
        $this->client->followRedirect();
    }

    public function testVendeurCannotAccessLogs(): void
    {
        // Crée un vendeur de test
        TestUserFactory::createVendeur($this->em, $this->hasher);

        // Login vendeur
        $this->loginAs('vendeur@test.com', 'vendeur123');

        // Route des logs (EasyAdmin)
        $this->client->request('GET', '/admin/action-log');

        // Le vendeur n'a PAS le droit de voir les logs
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminCanAccessLogs(): void
    {
        // Crée un admin de test
        TestUserFactory::createAdmin($this->em, $this->hasher);

        // Login admin
        $this->loginAs('admin@test.com', 'admin123');

        // Route des logs
        $this->client->request('GET', '/admin/action-log');

        // L'admin DOIT pouvoir y accéder
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }
}
