<?php

namespace Bookkeeper\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;
use Bookkeeper\UserBundle\Entity;

/**
 * Class DefaultControllerTest
 * @package Bookkeeper\UserBundle\Tests\Controller
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @codingStandardsIgnoreFile
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

    /**
     * @test
     */
    public function anonymous_role_can_access_login_action()
    {
        $this->client->request('GET', '/login');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    /**
     * @test
     */
    public function loggedin_user_is_redirected_in_login_path()
    {
        $this->logIn();

        $this->client->request('GET', '/login');

        $this->assertTrue(
            $this->client->getResponse()->isRedirect(),
            "GET request to /login is not redirected if user is logged-in"
        );
    }

    /**
     * @test
     */
    public function anonymous_role_is_redirected_in_logout_path()
    {
        $this->client->request('GET', '/logout');

        $this->assertTrue($this->client->getResponse()->isRedirect());
    }

    /**
     * @test
     */
    public function loggedin_user_is_redirected_in_logout_path()
    {
        $this->logIn();

        $this->client->request('GET', '/logout');

        $this->assertTrue($this->client->getResponse()->isRedirect());
    }
}
