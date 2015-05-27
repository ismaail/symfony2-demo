<?php

namespace Bookkeeper\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;
use Bookkeeper\UserBundle\Entity;

/**
 * Class DefaultControllerTest
 * @package Bookkeeper\UserBundle\Tests\Controller
 */
class DefaultControllerTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    public $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * Mock loggedIn user with role "ROLE_ADMIN"
     */
    private function logIn()
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $this->client->getContainer()->get('session');

        $firewall = 'secured_area';
        $token    = new UsernamePasswordToken('user', null, $firewall, array(Entity\User::ROLE_ADMIN));
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    public function testLoginPathIsAccessibleByAnonymousUser()
    {
        $this->client->request('GET', '/login');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testLoginPathIsRedirectedForLoggedInUser()
    {
        $this->logIn();

        $this->client->request('GET', '/login');

        $this->assertTrue(
            $this->client->getResponse()->isRedirect(),
            "GET request to /login is not redirected if user is logged-in"
        );
    }

    public function testLogoutPathIsRedirectedForAnonymousUser()
    {
        $this->client->request('GET', '/logout');

        $this->assertTrue($this->client->getResponse()->isRedirect());
    }

    public function testLogoutPathIsRedirectedForLoggedInUser()
    {
        $this->logIn();

        $this->client->request('GET', '/logout');

        $this->assertTrue($this->client->getResponse()->isRedirect());
    }
}
